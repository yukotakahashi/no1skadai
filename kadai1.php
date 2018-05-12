<?php
require 'vendor/autoload.php';


$consumer_key = '';
$consumer_secret = '';
$access_token = '';
$access_token_secret = '';

// twitterOAuthオブジェクト生成
$connection = new  \Abraham\TwitterOAuth\TwitterOAuth(
    $consumer_key, $consumer_secret, $access_token, $access_token_secret
);

// JustinBieberのキーワードでツイート取得
$tweet_param = [
      'q' => 'JustinBieber filter:images -rt',
      'result_type' => 'recent',
      'include_entities' => true,
      'count' => '10',
      'tweet_mode'  => 'extended'
];
$tweets = $connection->get('search/tweets', $tweet_param)->statuses;

$img_download_data = [];
foreach($tweets as $tweet) {
    // 画像が添付されているツイートを取得
    foreach($tweet->entities->media as $value) {
        if ($value->type === 'photo') {
          $img_download_data[] = $value;
        }
        break;
    }
}

// 画像つきのツイートが10件格納されている場合は保存処理に入る
if (count($img_download_data) === 10) {
    $download_dir = './download';
    // dounloadディレクトリ作成
    if (!file_exists($download_dir)) {
        mkdir($download_dir);
    }
    foreach ($img_download_data as $key => $img) {
        // 画像URL
        $img_url = file_get_contents($img->media_url);
        // 画像URLから拡張子を取り出す
        $img_ext = pathinfo($img->media_url, PATHINFO_EXTENSION);
        // var_dump($img_ext);die;
        // 画像を保存
        file_put_contents($download_dir.'/justinbieber_'.$key.'.'.$img_ext, $img_url);
    }
}


// echo '<PRE>';
// var_dump($img_download_data);die;
// echo '</PRE>';
