<?php
class TWCDCNEWS {
    const BASE_URL = 'https://www.cdc.gov.tw';
    const SEARCH_URL = 'https://www.cdc.gov.tw/Bulletin/List/MmgtpeidAR5Ooai4-fgHzQ';
    
    public function __construct($date="") {
        if ($date) { // search mode
            $date=date("Y.m.d",strtotime($date));
            $this->date=$date;
        }
        $this->page_load($home);
    }
    // 載入頁面, 有指定日期去搜尋, 無指定日期載入首頁
    public function page_load() {
        if ($this->date) {
            $data = array('PageSize' => '10',
                            'id' => '9',
                            'keyword' => '',
                            'startTime' => $this->date,
                            'endTime'=> $this->date);
            $request=http_build_query($data);
            $ch = curl_init (self::SEARCH_URL);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$request);
        } else { // homepage
            $ch = curl_init (self::BASE_URL);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res=curl_exec($ch);        
        file_put_contents("/tmp/cdcnews.html",$res); // for debug
        if (strstr($res,"cube-portfolio")||strstr($res,"js-grid-agency")) $this->have_link=true;
        $dom = new DOMDocument();
        $dom->loadHTML($res);
        $xpath = new DOMXPath($dom);
        $this->dom=$dom;
        $this->xpath=$xpath;
    }
    // 取得新聞稿 link, 傳回陣列, 含 title 及 url
    public function get_links() {
        $links=[];
        if ($this->have_link) {
            if ($this->date)
                $block = $this->xpath->query("//div[@class='cube-portfolio p-t-10 p-b-10']")[0];
            else
                $block = $this->dom->getElementById("js-grid-agency"); 
            foreach($block->getElementsByTagName('a') as $link) {
                $links[]=["url"=>self::BASE_URL.$link->getAttribute('href'),"title"=>$link->getAttribute('title')];
            }
        }
        return $links;
    }
    // 取得新聞稿內容, 傳回陣列, 含 subject 及 content
    public function get_content($url) {
        $res=file_get_contents($url);
        $dom_c = new DomDocument();
        $dom_c->loadHTML($res);
        $xpath_c=new DOMXPath($dom_c);
        $subject = trim($xpath_c->query("//meta[@property='og:title']")[0]->getAttribute('content'));
        preg_match("/發佈日期：([0-9-]{10})(.*)/sim",$dom_c->getElementsByTagName("section")[0]->textContent,$match);
        $content=trim("發佈日期：".trim($match[1])." ".trim($match[2]));
        return ["date"=>$match[1],"subject"=>$subject,"content"=>$content];
    }
    // 取得確診數新聞稿資訊，數據、摘要
    public function get_cases() {
        $arr['case']=0; $arr['local']=0; $arr['amend']=0; $arr['outside']=0; $arr['death']=0; $arr['date']=''; $arr['brief']='';
        $brief_union='';
        $n=0; // 比較舊的新聞稿本土、境外會分兩則, n=2 為滿足比對條件
        foreach (self::get_links() as $link) {
            //print_r($link); // debug
            if (preg_match("/新增.*例.*(個案|病例|確診|COVID-19)/",$link['title']) && !preg_match("/國內新增/",$link['title'])) {
                $content_arr=self::get_content($link['url']);
                $brief=strtok($content_arr['content'],"\n"); // 取第一段
                $brief_union.=$brief; 
                //中央流行疫情指揮中心今(18)日公布國內新增245例COVID-19確定病例，分別為240例本土及5例境外移入；確診個案中新增2例死亡（案1522、案2095）。
                $arr['date']=$content_arr['date'];
                //echo "brief=$brief\ncontent=$content\n"; // debug
                $news_id=basename(parse_url($link['url'])['path']);
                file_put_contents("/tmp/twcdcnews-".$news_id,"$brief\n$content\n",FILE_APPEND); // debug
                /* case 改最後加總
                if (preg_match("/([0-9]+)例COVID-19確定病例/sum", $brief, $match)) {
                    $arr['case']=$match[1]; unset($match);
                } elseif (preg_match("/新增([0-9]+)例COVID-19本土個案確定病例/sum", $brief, $match)) {
                    $arr['case']=$match[1]; unset($match);
                } elseif (preg_match("/新增([0-9]+)例本土COVID-19確定病例/sum", $brief, $match)) {
                    $arr['case']=$match[1]; unset($match);
                }
                */
                if (preg_match("/校正回歸本土個案([0-9]+)例/sum", $brief, $match)) {
                    $arr['amend']=$match[1]; unset($match);
                } elseif (preg_match("/另有([0-9]+)例本土個案為校正回歸/sum", $brief, $match)) {
                    $arr['amend']=$match[1]; unset($match);
                }
                if (strstr($brief,"均為本土")) {
                    preg_match("/([0-9]+)例.{0,2}COVID-19.{0,4}確定病例/sum", $brief, $match);
                    $arr['local']=$match[1]; unset($match);
                    $n=2;
                } elseif (strstr($brief,"均為境外")) {
                    preg_match("/([0-9]+)例.{0,2}COVID-19.{0,4}確定病例/sum", $brief, $match);
                    $arr['outside']=$match[1]; unset($match);
                    $n=2;
                } else {
                    if (preg_match("/([0-9]+)例.{0,1}本土/sum", $brief, $match)) {
                        $arr['local']=$match[1]; unset($match);
                        print_r($match);
                        $n++;
                    } elseif (preg_match("/([0-9]+)例COVID-19本土/sum", $brief, $match)) {
                        $arr['local']=$match[1]; unset($match);
                        $n++;
                    }
                    if (preg_match("/([0-9]+)例.{0,1}境外/sum", $brief, $match)) {
                        $arr['outside']=$match[1]; unset($match);
                        $n++;
                    } elseif (preg_match("/([0-9]+)例COVID-19境外/sum", $brief, $match)) {
                        $arr['outside']=$match[1]; unset($match);
                        $n++;
                    }
                }
                if (preg_match("/([0-9]+)例死亡/sum", $brief, $match)) $arr['death']=$match[1]; unset($match);
            }
            if ($n>=2 || ($arr['local'] && $arr['outside'])) break;
        }
        $arr['case']=$arr['local']+$arr['amend']+$arr['outside'];
        $arr['brief']=$brief_union;
        return $arr;
    }
}
?>