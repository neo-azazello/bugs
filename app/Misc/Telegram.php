<?php

namespace App\Misc;


class Telegram {
    
    protected $tokenid = "647912647:AAHfJtWUbme5Y9O_iUY9nleQAohT_RsSVA0";
    protected $channel = "@smartbugs";
    
    public function tg_msg($txt) {
        $token = $this->tokenid;
        $query = ["chat_id" => $this->channel, "text" => $txt];
        $url = "https://api.telegram.org/bot" . $token;
        $url .= "/sendMessage?" . http_build_query($query);
    
        $ch = curl_init($url);
        curl_exec($ch);
        curl_close($ch);
    }
    
    
}


