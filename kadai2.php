<?php
require 'vendor/autoload.php';

try {
    $domain_url = 'https://no1s.biz/';
    $url_list = [];
    $html_list = [];
    $pre_output_data = [];
    $output_data = [];
    $regex = '@<a[^>]*?(?<!\.)href="([^"]*+)"[^>]*+>(.*?)@si';

    // urlからhtml取得
    $html = file_get_contents($domain_url);
    if (!$html) {
        throw new Exception ('no html error');
    }
    $url_list[] = $domain_url;

    // 取得したhtmlからスクレイピングを行う
    $pre_data = search_link([$html], $output_data, $domain_url);

    // 同じドメインのURLを取得
    preg_match_all($regex, $html, $matches, PREG_SET_ORDER);
    if (count($matches) > 0) {
        foreach($matches as $match) {
            // URLを抽出するかチェック
            if (!check_url($match[1], $url_list, $domain_url)) {
              continue;
            }
            // 抽出対象になるHTMLを配列に格納
            $html = file_get_contents($match[1]);
            if (!$html) {
              throw new Exception ('no html error');
            }
            $url_list[] = $match[1];
            $html_list[] = $html;
        }
        // スクレイピングを実行し、表示するデータを取得
        $final_output_data = search_link($html_list, $pre_data, $domain_url);
        if (!count($final_output_data)) {
            throw new Exception('no data error');
        }

        // 取得したデータを表示
        foreach ($final_output_data as $data) {
          echo $data. PHP_EOL;
        }
    }

} catch (Exception $e) {
    echo 'ERROR:'.$e->getMessage();

}

/**
* 指定したURLからスクレイピングを行い、Aタグのリンク、titleタグを取得
**/
function search_link ($html_list, $output_data, $domain_url) {
    // 既に抽出されたデータからURLのみの配列を作成
    foreach ($html_list as $html) {
        // スクレイピング実行
        $dom = phpQuery::newDocument($html);
        // Aタグを取得
        $link_arr = pq($dom)->find('a');
        foreach ($link_arr as $arr) {
            // link先
            $link = pq($arr)->attr('href');
            // URLを抽出するかチェック
            if (!check_url($link, $output_data, $domain_url)) {
                continue;
            }
            $output_data[] = $link;
        }
        // titleタグを取得
        $title_arr = pq($dom)->find('title');
        foreach($title_arr as $t_arr) {
            $title = pq($t_arr)->text();
            if (in_array($title, $output_data)) {continue;}
            $output_data[] = $title;
        }

    }
    return $output_data;
}

/**
* 抽出するURLであるかどうかをチェック
**/
function check_url($target_url, $url_list, $domain_url) {
    // ドメインが違う、または既に抽出されたURLである場合は抽出しない
    if (strpos($target_url, $domain_url) === false || in_array($target_url, $url_list)) {
      return false;
    }
    return true;
}
