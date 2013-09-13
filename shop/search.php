<?php
include_once('./_common.php');

if (G5_IS_MOBILE) {
    include_once(G5_MSHOP_PATH.'/search.php');
    return;
}

$g5['title'] = "상품 검색 결과";
include_once('./_head.php');

// QUERY 문에 공통적으로 들어가는 내용
// 상품명에 검색어가 포한된것과 상품판매가능인것만
$sql_common = " from {$g5['g5_shop_item_table']} a,
                     {$g5['g5_shop_category_table']} b
               where a.ca_id=b.ca_id
                 and a.it_use = 1
                 and b.ca_use = 1
               /* 중복검색에 대한 오류로 인해 막음 : where (a.ca_id=b.ca_id or a.ca_id2=b.ca_id or a.ca_id3=b.ca_id) */ ";
if ($search_str) {
    $sql_common .= " and ( a.it_id like '$search_str%' or
                           a.it_name like   '%$search_str%' or
                           a.it_basic like  '%$search_str%' or
                           a.it_explan like '%$search_str%' ) ";
}
/*
// 공백을 구분하여 검색을 할때는 이 코드를 사용하십시오. or 조건
if ($search_str) {
    $s_str = explode(" ", $search_str);
    $or = " ";
    $sql_common .= " and ( ";
    for ($i=0; $i<count($s_str); $i++) {
        $sql_common .= " $or (a.it_id like '$s_str[$i]%' or a.it_name like '%$s_str[$i]%' or a.it_basic like  '%$s_str[$i]%' or a.it_explan like '%$s_str[$i]%' ) ";
        $or = " or ";
    }
    $sql_common .= " ) ";
}
*/

// 분류선택이 있다면 특정 분류만
if ($search_ca_id != "")
    $sql_common .= " and a.ca_id like '$search_ca_id%' ";

// 검색된 내용이 몇행인지를 얻는다
$sql = " select COUNT(*) as cnt $sql_common ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];
?>

<!-- 검색결과 시작 { -->
<div id="ssch">

    <div id="ssch_ov">검색어 <strong><?php echo ($search_str ? stripslashes(get_text($search_str)) : '없음'); ?></strong> | 검색 결과 <strong><?php echo $total_count; ?></strong>건</div>

    <?php
    // 임시배열에 저장해 놓고 분류별로 출력한다.
    // write_serarch_save() 함수가 임시배열에 있는 내용을 출력함
    if ($total_count > 0) {
        if (trim($search_str)) {
            // 인기검색어
            $sql = " insert into {$g5['popular_table']}
                        set pp_word = '$search_str',
                            pp_date = '".G5_TIME_YMD."',
                            pp_ip = '{$_SERVER['REMOTE_ADDR']}' ";
            sql_query($sql, FALSE);
        }

        unset($save); // 임시 저장 배열
        $sql = " select a.ca_id,
                        a.it_id
                 $sql_common
                 order by a.ca_id, a.it_id desc ";
        $result = sql_query($sql);
        for ($i=0; $row=mysql_fetch_array($result); $i++) {
            if ($save['ca_id'] != $row['ca_id']) {
                if ($save['ca_id']) {
                    write_search_save($save);
                    unset($save);
                }
                $save['ca_id'] = $row['ca_id'];
                $save['cnt'] = 0;
            }
            $save['it_id'][$save['cnt']] = $row['it_id'];
            $save[cnt]++;
        }
    }

    mysql_free_result($result);
    write_search_save($save);

    function write_search_save($save)
    {
        global $g5, $search_str , $default , $image_rate , $cart_dir;

        $sql = " select ca_name from {$g5['g5_shop_category_table']} where ca_id = '{$save['ca_id']}' ";
        $row = sql_fetch($sql);

        // 김선용 2006.12 : 중복 하위분류명이 많으므로 대분류 포함하여 출력
         $ca_temp = "";
         if(strlen($save['ca_id']) > 2) // 중분류 이하일 경우
         {
             $sql2 = " select ca_name from $g5[shop_category_table] where ca_id='".substr($save['ca_id'],0,2)."' ";
            $row2 = sql_fetch($sql2);
            $ca_temp = '<a href="./list.php?ca_id='.substr($save['ca_id'],0,2).'">'.$row2['ca_name'].'</a> &gt; ';
         }
    ?>
    <table class="basic_tbl">
    <caption><?php echo $ca_temp?><a href="./list.php?ca_id=<?php echo $save['ca_id']; ?>"><?php echo $row['ca_name']; ?></a> 상품<?php echo $save['cnt']; ?>개</caption>
    <thead>
    <tr>
        <th scope="col">이미지</td>
        <th scope="col">상품명</th>
        <th scope="col">판매가격</td>
        <th scope="col">포인트</td>
    </tr>
    </thead>

    <tbody>
    <?php
    for ($i=0; $i<$save['cnt']; $i++) {
        $sql = " select it_id,
                        it_name,
                        it_price,
                        it_tel_inq,
                        it_point,
                        it_type1,
                        it_type2,
                        it_type3,
                        it_type4,
                        it_type5
                   from {$g5['g5_shop_item_table']} where it_id = '{$save['it_id'][$i]}' ";
        $row = sql_fetch($sql);

        $image = get_it_image($row['it_id'], (int)($default['de_simg_width']), (int)($default['de_simg_height']), true);
    ?>
    <tr>
        <td class="ssch_it_img"><?php echo $image; ?></td>
        <td><?php echo get_text($row['it_name']); ?></td>
        <td class="ssch_num"><?php echo display_price(get_price($row), $row['it_tel_inq']); ?></td>
        <td class="ssch_num"><?php echo display_point($row['it_point']); ?></td>
    </tr>
    <?php } // for 끝 ?>
    </tbody>
    </table>
    <?php } // function 끝 ?>

</div>
<!-- } 검색결과 끝 -->

<?php
include_once('./_tail.php');
?>
