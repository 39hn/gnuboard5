<?php
if (!defined('_GNUBOARD_')) exit;

// 최신글 추출
function latest($skin_dir='', $bo_table, $rows=10, $subject_len=40)
{
    global $g5;
    static $css = array();

    if (!$skin_dir) $skin_dir = 'basic';

    if(G5_IS_MOBILE) {
        $latest_skin_path = G5_MOBILE_PATH.'/'.G5_SKIN_DIR.'/latest/'.$skin_dir;
        $latest_skin_url  = G5_MOBILE_URL.'/'.G5_SKIN_DIR.'/latest/'.$skin_dir;
    } else {
        $latest_skin_path = G5_SKIN_PATH.'/latest/'.$skin_dir;
        $latest_skin_url  = G5_SKIN_URL.'/latest/'.$skin_dir;
    }

    $cache_file = G5_DATA_PATH."/cache/latest-{$bo_table}-{$skin_dir}-{$rows}-{$subject_len}.php";
    if (!G5_USE_CACHE || !file_exists($cache_file)) {
        $list = array();

        $sql = " select * from {$g5['board_table']} where bo_table = '{$bo_table}' ";
        $board = sql_fetch($sql);

        $tmp_write_table = $g5['write_prefix'] . $bo_table; // 게시판 테이블 전체이름
        $sql = " select * from {$tmp_write_table} where wr_is_comment = 0 order by wr_num limit 0, {$rows} ";
        $result = sql_query($sql);
        for ($i=0; $row = sql_fetch_array($result); $i++) {
            $list[$i] = get_list($row, $board, $latest_skin_url, $subject_len);
        }

        $handle = fopen($cache_file, 'w');
        $cache_content = "<?php\nif (!defined('_GNUBOARD_')) exit;\n\$bo_subject=\"".get_text($board['bo_subject'])."\";\n\$list=".var_export($list, true)."?>";
        fwrite($handle, $cache_content);
        fclose($handle);
    }

    include_once($cache_file);

    /*
    // 같은 스킨은 .css 를 한번만 호출한다.
    if (!in_array($skin_dir, $css) && is_file($latest_skin_path.'/style.css')) {
        echo '<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">';
        $css[] = $skin_dir;
    }
    */

    ob_start();
    include $latest_skin_path.'/latest.skin.php';
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}
?>
