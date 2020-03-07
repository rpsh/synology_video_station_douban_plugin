<?php

define('DOUBAN_SEARCH_API', 'https://movie.douban.com/j/subject_suggest?q=');
define('DOUBAN_API', 'https://douban.uieee.com/v2/movie/subject/');

function fixDoubanPicURL($url){
    if($url){
        $new_url = preg_replace('/\.webp$/','.jpg', $url);
        $new_url = preg_replace('/\/s_ratio_poster\//','/l/', $new_url); //替换为 large 格式的图片
        // $new_url = preg_replace('/\/l\//','/raw/', $new_url); //没有登录态时候， raw 的图片 403
        return  $new_url;
    }
    return $url;
}

function fixAvailableDate($str, $year){
    if($str){
        // 豆瓣发布日期格式是： 2019-12-25(美国)
        return preg_replace('/\([^)]*\)$/', '', $str);
    }else if($year){
        return $year;
    }
}

function GetMovieInfoDouban($movie_data, $data)
{
    if( !isset($movie_data->aka) ) $movie_data->aka = array();

    if (isset($movie_data->current_season)) {
        $data['season']		= $movie_data->current_season;
        // $data['title']      = preg_replace('/\s第[^季]季$/', '', $movie_data->title);
    }

	$data['title']				 	= $movie_data->title;
	$data['original_title']			= $movie_data->original_title;
	$data['tagline'] 				= implode(',', $movie_data->aka);
	$data['original_available'] 	= fixAvailableDate($movie_data->pubdates[0], $movie_data->year);
	$data['summary'] 				= $movie_data->summary;
    $data['id'] = $movie_data->id;

    //extra
    $data['extra'] = array();
    $data['extra'][PLUGINID] = array('reference' => array());
    $data['extra'][PLUGINID]['reference']['themoviedb'] = $movie_data->id;
    $data['doubandb'] = true;

    if (isset($movie_data->imdb_id)) {
        $data['extra'][PLUGINID]['reference']['imdb'] = $movie_data->imdb_id;
    }
    if ((float)$movie_data->rating->average) {
		$data['extra'][PLUGINID]['rating'] = array('themoviedb' => (float)$movie_data->rating->average);
	}
    if (isset($movie_data->images)) {
		 $data['extra'][PLUGINID]['poster'] = array(fixDoubanPicURL($movie_data->images->large));
	}

    if (isset($movie_data->photos)) {
        $data['extra'][PLUGINID]['backdrop'] = array(fixDoubanPicURL($movie_data->photos[0]->image));
    }
    // if (isset($movie_data->belongs_to_collection)) {
    //     $data['extra'][PLUGINID]['collection_id'] = array('themoviedb' => $movie_data->belongs_to_collection->id);
    // }

    // genre
    if( isset($movie_data->genres) ){
        $data['genre'] = array();
		foreach ($movie_data->genres as $item) {
			array_push($data['genre'], $item);
		}
	}

    // actor
	if( isset($movie_data->casts) ){
        $data['actor'] = array();
		foreach ($movie_data->casts as $item) {
			array_push($data['actor'], $item->name);
		}
	}

	// director
	if( isset($movie_data->directors) ){
        $data['director'] = array();
		foreach ($movie_data->directors as $item) {
			array_push($data['director'], $item->name);
		}
	}

	// writer
	if( isset($movie_data->writers) ){
        $data['writer'] = array();
		foreach ($movie_data->writers as $item) {
			array_push($data['writer'], $item->name);
		}
	}

    //error_log(print_r( $data, true), 3, "/var/packages/VideoStation/target/plugins/syno_themoviedb/my-errors.log");
    return $data;
}

/**
 * @brief get metadata for multiple movies
 * @param $query_data [in] a array contains multiple movie item
 * @param $lang [in] a language
 * @return [out] a result array
 */
function GetMetadataDouban($query_data, $lang)
{
    global $DATA_TEMPLATE;

    //Foreach query result
    $result = array();
    foreach ($query_data as $item) {
        //Copy template
        $data = $DATA_TEMPLATE;
        //Get movie
        $movie_data = json_decode( HTTPGETRequest(DOUBAN_API . $item['id']) );
        //error_log(print_r( $movie_data, true), 3, "/var/packages/VideoStation/target/plugins/syno_themoviedb/my-errors.log");
        if (!$movie_data) {
            continue;
        }
        $data = GetMovieInfoDouban($movie_data, $data);
        //Append to result
        $result[] = $data;
    }

    return $result;
}
function test($title, $lang)
{
    if (!function_exists('HTTPGETRequest')) {
        function HTTPGETRequest($url)
        {
            return file_get_contents($url);
        }
    }
    $query_data = array();
    $query_data = json_decode( HTTPGETRequest(DOUBAN_SEARCH_API . $title ), true );

    //Get metadata
    return GetMetadataDouban(array_slice($query_data, 0, 3), $lang);
}
// print_r(test('阳光普照', 'chs'));
