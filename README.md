# twcdcnews
Taiwan CDC News (台灣衛福部疾管署新聞稿) PHP Class 及歷史資料 (2021之後)

### new TWCDCNEWS() 裡面可不帶參數或帶日期參數, 格式 YYYY-mm-dd
    include("twcdcnews-class.php");
    $cdc = new TWCDCNEWS(); // 查首頁資訊
    // 或
    $cdc = new TWCDCNEWS("2021-06-26"); // 指定日期搜尋新聞搞

### 回傳新聞稿的 link, 傳回陣列, 含 title 及 url 兩個欄位
    $cdc->get_links(); 
#### 回傳陣列內容範例
    Array
    (
        [0] => Array
            (
                [url] => https://www.cdc.gov.tw/Bulletin/Detail/5NpKu8jZAKdJssJnlGxTXA?typeid=9
                [title] => 6月25日全國防疫會議後記者會報告
            )

        [1] => Array
            (
                [url] => https://www.cdc.gov.tw/Bulletin/Detail/a_nNEk7-jcJcUMqE3M-Xeg?typeid=9
                [title] => 為有效利用COVID-19疫苗 當日最後一瓶開瓶剩餘劑量將開放候補接種
            )

        [2] => Array
            (
                [url] => https://www.cdc.gov.tw/Bulletin/Detail/zqUqseKSNKJint4YMEddiQ?typeid=9
                [title] => 因應全球Delta變異株流行，自6月27日零時起，全面提升入境人員檢疫措施
            )

        [3] => Array
            (
                [url] => https://www.cdc.gov.tw/Bulletin/Detail/9pKIzKzZOpp4HocU3uCl6g?typeid=9
                [title] => 新增76例COVID-19確定病例，均為本土個案
            )

    )

### 帶入 get_links() 取回的 url, 取得新聞稿內容, 含 data, subject, content 三個欄位
    $cdc->get_content($url); 
#### 回傳陣列內容範例
    Array
    (
        [date] => 2021-06-25
        [subject] => 新增76例COVID-19確定病例，均為本土個案
        [content] => 發佈日期：2021-06-25 中央流行疫情指揮中心今(25)日公布國內新增76例COVID-19確定病例，均為本土個案；其中34例為居家隔離/檢疫期間或期滿檢驗陽性者。另，確診個案中新增5例死亡。

    指揮中心表示，今日新增之76例本土病例，為39例男性、37例女性，年齡介於未滿5歲至80多歲，發病日介於今(2021)年5月18日至6月24日。後略

    )

### 取得確診數新聞稿資訊，數據、摘要, 含 case 等七個欄位
    $cdc->get_cases();
    // case 確診數
    // local 本土案例
    // amend 校正回歸
    // outside 境外移入
    // death 死亡案例
    // date 日期
    // brief 摘要
#### 回傳陣列內容範例
    Array
    (
        [case] => 76
        [local] => 76
        [amend] => 0
        [outside] => 0
        [death] => 5
        [date] => 2021-06-25
        [brief] => 發佈日期：2021-06-25 中央流行疫情指揮中心今(25)日公布國內新增76例COVID-19確定病例，均為本土個案；其中34例為居家隔離/檢疫期間或期滿檢驗陽性者。另，確診個案中新增5例死亡。
    )
另外 data 目錄裡面有 2021 之後的確診數資料，每日的 json 檔，
today.json 是今天的資料，
2021.json 2021.csv 是累積的資料，每天自動更新。
