<?php

namespace App\Controllers;

class FileController extends Controller
{
    public function addFiles($request, $response)
    {
        if (!empty($request->getUploadedFiles()['taskfiles'][0]->getClientFilename())) {

            $directory = $this->container->get('upload_directory');
            $uploadedFiles = $request->getUploadedFiles();

            foreach ($uploadedFiles['taskfiles'] as $uploadedFile) {
                if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                    $filename = $this->moveUploadedFile($directory, $uploadedFile);
                    $insert_bulk_files[] = array('taskid' => $request->getParam('taskid'), 'filename' => $filename);
                }

            }

            $this->container->db->table('taskfiles')->insert($insert_bulk_files);
        }
    }

    public function moveUploadedFile($directory, $uploadedFile)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8));
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        return $filename;
    }

    public function deleteTaskFile($request, $response)
    {

        $filename = $this->container->db->table('taskfiles')
            ->select('filename')
            ->where('fileid', $request->getParam('file'))
            ->value('filename');

        $directory = $this->container->get('upload_directory');
        unlink($directory . "/" . $filename);

        $this->container->db->table('taskfiles')->where('fileid', $request->getParam('file'))->delete();
    }
}
