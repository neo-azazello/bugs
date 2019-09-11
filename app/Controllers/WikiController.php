<?php

namespace App\Controllers;

class WikiController extends Controller
{

    //CREATES TREE FROM PLAIN LIST
    private function getChildren($source)
    {
        $childs = array();

        foreach ($source as $item) {
            $childs[$item->parentid][] = $item;
        }

        foreach ($source as $item) {
            if (isset($childs[$item->wikimenuid])) {
                $item->children = $childs[$item->wikimenuid];
            }
        }

        return (!empty($childs[0])) ? $childs[0] : false;
    }

    private function getProjectId($projectname)
    {
        return $this->container->db
            ->table('projects')
            ->where('projectname', $projectname)
            ->first();
    }

    private function getProjectName($wikislug)
    {
        return $this->container->db
            ->table('projects')
            ->where('wikimenu.wikimenuslug', $wikislug)
            ->join('wikimenu', 'wikimenu.projectid', '=', 'projects.projectid')
            ->first();
    }

    public function loadWikiPage($request, $response, $arg)
    {
        $data['article'] = $this->container->db
            ->table('wikiarticle')

            ->first();
        $data['projectname'] = $arg['projectname'];
        $data['wikimenu'] = $this->container->db
            ->table('wikimenu')
            ->where('projectid', $this->getProjectId($data['projectname'])->projectid)
            ->get();
        $data['menu'] = $this->getChildren($data['wikimenu']);

        return $this->view->render($response, 'wiki/main.twig', $data);
    }

    public function addWikiMenu($request, $response)
    {
        $wikimenuslug = time();
        $project = $this->getProjectId($request->getParam('projectname'));

        if (!empty($request->getParam('parentid'))) {
            $parentid = $request->getParam('parentid');
        } else {
            $parentid = 0;
        }

        $this->container->db
            ->table('wikimenu')
            ->insert(array(
                'wikimenuname' => $request->getParam('wikimenuname'),
                'parentid' => $parentid,
                'projectid' => $project->projectid,
                'wikimenuslug' => $wikimenuslug,
            ));

        return $response->withRedirect($this->router->pathFor('getwiki', [
            'projectname' => $request->getParam('projectname'),
            'wikislug' => $wikimenuslug])
        );
    }

    public function getWikiArticle($request, $response, $arg)
    {
        $data['projectname'] = $arg['projectname'];
        $data['wikislug'] = $arg['wikislug'];

        $data['article'] = $this->container->db
            ->table('wikiarticle')
            ->where('wikimenuslug', $data['wikislug'])
            ->first();

        $data['wikimenu'] = $this->container->db
            ->table('wikimenu')
            ->where('projectid', $this->getProjectId($data['projectname'])->projectid)
            ->get();
        $data['menu'] = $this->getChildren($data['wikimenu']);

        return $this->view->render($response, 'wiki/wikiarticle.twig', $data);
    }

    public function addWikiArticle($request, $response)
    {
        $this->container->db
            ->table('wikiarticle')
            ->insert(array(
                'wikitext' => $request->getParam('wikitext'),
                'wikimenuslug' => $request->getParam('wikimenuslug'),
            ));

        return $response->withRedirect($this->router->pathFor('getwiki', [
            'projectname' => $request->getParam('projectname'),
            'wikislug' => $request->getParam('wikimenuslug')])
        );
    }

    public function editWikiArticle($request, $response)
    {
        $args['data'] = $this->container->db
            ->table('wikiarticle')
            ->where('wikiarticleid', $request->getParam('wikiarticleid'))
            ->first();
        return $this->view->render($response, 'wiki/modals/editarticle.twig', $args);
    }

    public function updateWikiArticle($request, $response)
    {
        $this->container->db
            ->table('wikiarticle')
            ->where('wikiarticleid', $request->getParam('wikiarticleid'))
            ->update(array(
                'wikitext' => $request->getParam('wikitext'),
                'wikimenuslug' => $request->getParam('wikimenuslug'),
            ));

        return $response->withRedirect($this->router->pathFor('getwiki', [
            'projectname' => $this->getProjectName($request->getParam('wikimenuslug'))->projectname,
            'wikislug' => $request->getParam('wikimenuslug')])
        );
    }
}
