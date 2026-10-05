<?php

$db_name = "mysql:host=localhost:3307;dbname=shop_db";
$username = "root";
$password = "";

$conn = new PDO($db_name, $username, $password);

$serpapi_keys = [
    '67eb1066192cf45dcdedc15294f0d583d1eaf2d3e04ddd1cb60457c671b291e8',
    'aae0aaa00194b0c76fbe9e665ce35a448cf4fb44e8f1ea0d6387661136c46d65',
    '6b7989933074a7da15bdcac4c7e8389f494338aa05085a0c332bdf3bf564ab88',
    '7cda8d8e64802e0c6271d735bc49c650f95b12088b801ac9a4d280719bfc9335',
    'cf4d9d491a9c5e3e1cd1d9413117fb71ab17282b487362322ec214565de0621f',
    '305346d5ceea6cadc0f547ba4668705c57991a172478bc27e296e73ca0b17797',
    'fd7bd6e3016d4f0ab0a1499b92a7d4b5a477c0be07f7213c49f28b0d861a1f52',
    'a3363ed543720799d91d6e17c5acfd7291170b0413b698ec3f1d37019cdb5ed8',
    '9119155bb385f662a57edf1b644c9506db63357c6e85ac697ddbac9a46f751ee',
    'ba915bfd0bf9f07f1363e74a0a4f609021e04aec55ab1f2d1232a61623438fcb',
    'd81981744b5323a82bc456c5601fe843866123b462e2dcd2f74a9611278dfd90',
    '514070711066758ea12be64c0b5cf453db555fba899536f042de9fe9c0657387',
    '265000ef851a7d4cb5ce5042eff3a4ab8d9cedac7bdfb1e257a548304ac67d44',
    'a9f0363551aef871e75b4ffcfa1c59bb83041af5d97ca6121fc079ffc8355225',
    '29ca83eaca74f7dbdbc904294e7403af1292cdec451fdd44a2ebf0c029d965eb',
    '67a6aab9d18bb8b15c35056a9eb8fa641919193b563398f748fe9a8c65b041b7',
    // add more keys if you have
];


/**
 * Fetches data from SerpAPI with multiple API keys fallback.
 * @param string $query The search query.
 * @param array $keys The array of API keys.
 * @return array|false The decoded JSON data or false if all fail.
 */

function fetchFromSerpApiWithFallback($query, $keys)
{
    $endpoint_base = "https://serpapi.com/search.json?engine=google_shopping&q=" . urlencode($query);

    foreach ($keys as $key) {
        $url = $endpoint_base . "&api_key=" . $key;
        $response = @file_get_contents($url);
        if ($response === false) {
            continue;
        }
        $data = json_decode($response, true);

        if (isset($data['error'])) {
            $error = strtolower($data['error']);
            if (strpos($error, 'quota') !== false || strpos($error, 'limit') !== false) {
                continue;
            }
        }

        return $data;
    }

    return false;
}
