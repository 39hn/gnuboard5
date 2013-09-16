<?php
$sub_menu = "300100";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$sql_common = " from {$g5['board_table']} a ";
$sql_search = " where (1) ";

if ($is_admin != "super") {
    $sql_common .= " , {$g5['group_table']} b ";
    $sql_search .= " and (a.gr_id = b.gr_id and b.gr_admin = '{$member['mb_id']}') ";
}

if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case "bo_table" :
            $sql_search .= " ($sfl like '$stx%') ";
            break;
        case "a.gr_id" :
            $sql_search .= " ($sfl = '$stx') ";
            break;
        default :
            $sql_search .= " ($sfl like '%$stx%') ";
            break;
    }
    $sql_search .= " ) ";
}

if (!$sst) {
    $sst  = "a.gr_id, a.bo_table";
    $sod = "asc";
}
$sql_order = " order by $sst $sod ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$listall = '';
if ($sfl || $stx) // 검색렬일 때만 처음 버튼을 보여줌
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">전체목록</a>';

$g5['title'] = '게시판관리';
include_once('./admin.head.php');

$colspan = 16;
?>

<form name="fsearch" id="fsearch" method="get">
<fieldset>
    <legend>게시판 검색</legend>
    <span>
        <?php echo $listall ?>
        생성된 게시판수 <?php echo number_format($total_count) ?>개
    </span>
    <select name="sfl" title="검색대상">
        <option value="bo_table"<?php echo get_selected($_GET['sfl'], "bo_subject", true); ?>>TABLE</option>
        <option value="bo_subject"<?php echo get_selected($_GET['sfl'], "bo_subject"); ?>>제목</option>
        <option value="a.gr_id"<?php echo get_selected($_GET['sfl'], "a.gr_id"); ?>>그룹ID</option>
    </select>
    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" required class="required frm_input">
    <input type="submit" value="검색" class="btn_submit">
</fieldset>
</form>

<section class="cbox">
    <h2>생성된 게시판 목록</h2>
    <p>여러개의 게시판 설정을 한번에 바꾸실 때는 게시판 체크기능을 이용하세요.</p>

    <?php if ($is_admin == 'super') { ?>
    <div class="btn_add sort_with">
        <a href="./board_form.php" id="bo_add">게시판 추가</a>
    </div>
    <?php } ?>

    <ul class="sort_odr">
        <li><?php echo subject_sort_link('a.gr_id') ?>그룹<span class="sound_only"> 순 정렬</span></a></li>
        <li><?php echo subject_sort_link('bo_table') ?>TABLE<span class="sound_only"> 순 정렬</span></a></li>
        <li><?php echo subject_sort_link('bo_skin', '', 'desc') ?>스킨<span class="sound_only"> 순 정렬</span></a></li>
        <li><?php echo subject_sort_link('bo_mobile_skin', '', 'desc') ?>모바일<span class="sound_only"> 스킨 순 정렬</span></a></li>
        <li><?php echo subject_sort_link('bo_subject') ?>제목<span class="sound_only"> 순 정렬</span></a></li>
        <li><?php echo subject_sort_link('bo_use_sns') ?>SNS<span class="sound_only"> 순 정렬</span></a></li>
        <li><?php echo subject_sort_link('bo_use_search') ?>검색사용<span class="sound_only"> 순 정렬</span></a></li>
        <li><?php echo subject_sort_link('bo_show_menu') ?>메뉴보임<span class="sound_only"> 순 정렬</span></a></li>
        <li><?php echo subject_sort_link('bo_order') ?>출력순서<span class="sound_only"> 순 정렬</span></a></li>
    </ul>

    <form name="fboardlist" id="fboardlist" action="./board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="<?php echo $token ?>">
    <table class="tbl_bo_list">
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">게시판 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">그룹</th>
        <th scope="col">TABLE</th>
        <th scope="col">스킨</th>
        <th scope="col">모바일<br>스킨</th>
        <th scope="col">제목</th>
        <th scope="col">읽기P<span class="sound_only">포인트</span></th>
        <th scope="col">쓰기P<span class="sound_only">포인트</span></th>
        <th scope="col">댓글P<span class="sound_only">포인트</span></th>
        <th scope="col">다운P<span class="sound_only">포인트</span></th>
        <th scope="col">SNS<br>사용</th>
        <th scope="col">검색<br>사용</th>
        <th scope="col">메뉴<br>보임</th>
        <th scope="col">출력<br>순서</th>
        <th scope="col">접속기기</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $one_update = '<a href="./board_form.php?w=u&amp;bo_table='.$row['bo_table'].'&amp;'.$qstr.'">수정</a>';
        $one_copy = '<a href="./board_copy.php?bo_table='.$row['bo_table'].'" class="board_copy" target="win_board_copy">복사</a>';
    ?>

    <tr>
        <td>
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['bo_subject']) ?></label>
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
        </td>
        <td>
            <?php if ($is_admin == 'super'){ ?>
                <?php echo get_group_select("gr_id[$i]", $row['gr_id']) ?>
            <?php }else{ ?>
                <input type="hidden" name="gr_id[<?php echo $i ?>]" value="<?php echo $row['gr_id'] ?>"><?php echo $row['gr_subject'] ?>
            <?php } ?>
        </td>
        <td class="td_boid">
            <input type="hidden" name="board_table[<?php echo $i ?>]" value="<?php echo $row['bo_table'] ?>">
            <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=<?php echo $row['bo_table'] ?>"><?php echo $row['bo_table'] ?></a>
        </td>
        <td>
            <?php echo get_skin_select('board', 'bo_skin_'.$i, "bo_skin[$i]", $row['bo_skin']); ?>
        </td>
        <td>
            <?php echo get_mobile_skin_select('board', 'bo_mobile_skin_'.$i, "bo_mobile_skin[$i]", $row['bo_mobile_skin']); ?>
        </td>
        <td><input type="text" name="bo_subject[<?php echo $i ?>]" value="<?php echo get_text($row['bo_subject']) ?>" id="bo_subject[<?php echo $i ?>]" title="게시판제목" class="required frm_input" size="10" required="required"></td>
        <td><input type="text" name="bo_read_point[<?php echo $i ?>]" value="<?php echo $row['bo_read_point'] ?>"  title="읽기포인트" class="frm_input" size="2"></td>
        <td><input type="text" name="bo_write_point[<?php echo $i ?>]" value="<?php echo $row['bo_write_point'] ?>" title="쓰기포인트" class="frm_input" size="2"></td>
        <td><input type="text" name="bo_comment_point[<?php echo $i ?>]" value="<?php echo $row['bo_comment_point'] ?>" title="댓글포인트" class="frm_input" size="2"></td>
        <td><input type="text" name="bo_download_point[<?php echo $i ?>]" value="<?php echo $row['bo_download_point'] ?>" title="다운포인트" class="frm_input" size="2"></td>
        <td><input type="checkbox" name="bo_use_sns[<?php echo $i ?>]" value="1" id="bo_use_sns<?php echo $i ?>" <?php echo $row['bo_use_sns']?"checked":"" ?> title="선택시 SNS 사용"></td>
        <td><input type="checkbox" name="bo_use_search[<?php echo $i ?>]" value="1" id="bo_use_search_<?php echo $i ?>" <?php echo $row['bo_use_search']?"checked":"" ?> title="선택시 검색사용"></td>
        <td><input type="checkbox" name="bo_show_menu[<?php echo $i ?>]" value="1" id="bo_show_menu_<?php echo $i ?>" <?php echo $row['bo_show_menu']?"checked":"" ?> title="선택시 메뉴보이기"></td>
        <td><input type="text" name="bo_order[<?php echo $i ?>]" value="<?php echo $row['bo_order'] ?>" id="bo_order<?php echo $i ?>" title="출력순서" class="frm_input" size="2"></td>
        <td>
            <select name="bo_device[<?php echo $i ?>]" id="bo_device_<?php echo $i ?>" title="접속기기 선택">
                <option value="both"<?php echo get_selected($row['bo_device'], 'both', true); ?>>모두</option>
                <option value="pc"<?php echo get_selected($row['bo_device'], 'pc'); ?>>PC</option>
                <option value="mobile"<?php echo get_selected($row['bo_device'], 'mobile'); ?>>모바일</option>
            </select>
        </td>
        <td><?php echo $one_update ?> <?php echo $one_copy ?></td>
    </tr>
    <?php
    }
    if ($i == 0)
        echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>

    <div class="btn_list">
        <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value">
        <?php if ($is_admin == 'super') { ?>
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value">
        <?php } ?>
    </div>

    </form>
</section>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, $_SERVER['PHP_SELF'].'?'.$qstr.'&amp;page='); ?>

<script>
function fboardlist_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    return true;
}

$(function(){
    $(".board_copy").click(function(){
        window.open(this.href, "win_board_copy", "left=100,top=100,width=550,height=450");
        return false;
    });
});
</script>

<?php
include_once('./admin.tail.php');
?>
