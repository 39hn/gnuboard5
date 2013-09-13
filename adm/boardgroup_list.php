<?php
$sub_menu = "300200";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

if (!isset($group['gr_device'])) {
    // 게시판 그룹 사용 필드 추가
    // both : pc, mobile 둘다 사용
    // pc : pc 전용 사용
    // mobile : mobile 전용 사용
    // none : 사용 안함
    sql_query(" ALTER TABLE  `{$g5['board_group_table']}` ADD  `gr_device` ENUM(  'both',  'pc',  'mobile' ) NOT NULL DEFAULT  'both' AFTER  `gr_subject` ", false);
}

$sql_common = " from {$g5['group_table']} ";

$sql_search = " where (1) ";
if ($is_admin != 'super')
    $sql_search .= " and (gr_admin = '{$member['mb_id']}') ";

if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case "gr_id" :
        case "gr_admin" :
            $sql_search .= " ({$sfl} = '{$stx}') ";
            break;
        default :
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if ($sst)
    $sql_order = " order by {$sst} {$sod} ";
else
    $sql_order = " order by gr_id asc ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$listall = '';
if ($sfl || $stx) // 검색렬일 때만 처음 버튼을 보여줌
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">처음</a>';

$g5['title'] = '게시판그룹설정';
include_once('./admin.head.php');

$colspan = 11;
?>

<form id="fsearch" name="fsearch" method="get">
<fieldset>
    <legend>그룹 검색</legend>
    <span>
        <?php echo $listall ?>
        생성된 그룹수 <?php echo number_format($total_count) ?>개
    </span>
    <select name="sfl" title="검색대상">
        <option value="gr_subject"<?php echo get_selected($_GET['sfl'], "gr_subject"); ?>>제목</option>
        <option value="gr_id"<?php echo get_selected($_GET['sfl'], "gr_id"); ?>>ID</option>
        <option value="gr_admin"<?php echo get_selected($_GET['sfl'], "gr_admin"); ?>>그룹관리자</option>
    </select>
    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx ?>" required class="required frm_input">
    <input type="submit" value="검색" class="btn_submit">
</fieldset>
</form>

<section class="cbox">
    <h2>게시판그룹 목록</h2>
    <p>
        접근사용 옵션을 설정하시면 관리자가 지정한 회원만 해당 그룹에 접근할 수 있습니다.<br>
        접근사용 옵션은 해당 그룹에 속한 모든 게시판에 적용됩니다.
    </p>

    <?php if ($is_admin == 'super') { ?>
    <div class="btn_add sort_with">
        <a href="./boardgroup_form.php" id="bo_gr_add">게시판그룹 추가</a>
    </div>
    <?php } ?>

    <ul class="sort_odr">
        <li><?php echo subject_sort_link('gr_id') ?>그룹아이디<span class="sound_only"> 순 정렬</span></a></th>
        <li><?php echo subject_sort_link('gr_subject') ?>제목<span class="sound_only"> 순 정렬</span></a></th>
        <?php if ($is_admin == 'super'){ ?><li><?php echo subject_sort_link('gr_admin') ?>그룹관리자<span class="sound_only"> 순 정렬</span></a></th><?php } ?>
        <li><?php echo subject_sort_link('gr_order') ?>출력순서<span class="sound_only"> 순 정렬</span></a></th>
    </ul>

    <form name="fboardgrouplist" id="fboardgrouplist" action="./boardgroup_list_update.php" onsubmit="return fboardgrouplist_submit(this);" method="post">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="<?php echo $token ?>">

    <table class="tbl_gr_list">
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">그룹 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">그룹아이디</th>
        <th scope="col">제목</th>
        <?php if ($is_admin == 'super'){ ?><th scope="col">그룹관리자</th><?php } ?>
        <th scope="col">게시판<br>갯수</th>
        <th scope="col">접근<br>사용</th>
        <th scope="col">접근<br>회원수</th>
        <th scope="col">메뉴<br>보임</th>
        <th scope="col">출력<br>순서</th>
        <th scope="col">접속기기</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        // 접근회원수
        $sql1 = " select count(*) as cnt from {$g5['group_member_table']} where gr_id = '{$row['gr_id']}' ";
        $row1 = sql_fetch($sql1);

        // 게시판수
        $sql2 = " select count(*) as cnt from {$g5['board_table']} where gr_id = '{$row['gr_id']}' ";
        $row2 = sql_fetch($sql2);

        $s_upd = '<a href="./boardgroup_form.php?'.$qstr.'&amp;w=u&amp;gr_id='.$row['gr_id'].'">수정</a>';
    ?>

    <tr>
        <td class="td_chk">
            <input type="hidden" name="group_id[<?php echo $i ?>]" value="<?php echo $row['gr_id'] ?>">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo $row['gr_subject'] ?> 그룹</label>
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
        </td>
        <td class="td_grid"><a href="<?php echo G5_BBS_URL ?>/group.php?gr_id=<?php echo $row['gr_id'] ?>"><?php echo $row['gr_id'] ?></a></td>
        <td>
            <input type="text" name="gr_subject[<?php echo $i ?>]" value="<?php echo get_text($row['gr_subject']) ?>" id="gr_subject_<?php echo $i ?>" title="그룹제목 수정" class="frm_input">
        </td>
        <td>
        <?php if ($is_admin == 'super'){ ?>
            <input type="text" name="gr_admin[<?php echo $i ?>]" value="<?php echo $row['gr_admin'] ?>" id="gr_admin_<?php echo $i ?>" title="그룹관리자 수정" class="frm_input" size="10" maxlength="20">
        <?php }else{ ?>
            <input type="hidden" name="gr_admin[<?php echo $i ?>]" value="<?php echo $row['gr_admin'] ?>"><td><?php echo $row['gr_admin'] ?>
        <?php } ?>
        </td>
        <td><a href="./board_list.php?sfl=a.gr_id&amp;stx=<?php echo $row['gr_id'] ?>"><?php echo $row2['cnt'] ?></a></td>
        <td><input type="checkbox" name="gr_use_access[<?php echo $i ?>]" <?php echo $row['gr_use_access']?'checked':'' ?> value="1" id="gr_use_access_<?php echo $i ?>" title="선택 시 접근회원 사용"></td>
        <td><a href="./boardgroupmember_list.php?gr_id=<?php echo $row['gr_id'] ?>"><?php echo $row1['cnt'] ?></a></td>
        <td><input type="checkbox" name="gr_show_menu[<?php echo $i ?>]" <?php echo $row['gr_show_menu']?'checked':'' ?> value="1" id="gr_show_menu_<?php echo $i ?>" title="선택 시 메뉴보이기"></td>
        <td>
            <input type="text" name="gr_order[<?php echo $i ?>]" value="<?php echo $row['gr_order'] ?>" id="gr_order_<?php echo $i ?>" title="출력순서 수정" class="frm_input" size="2">
        </td>
        <td>
            <select id="gr_device_<?php echo $i ?>" name="gr_device[<?php echo $i ?>]" title="접속기기 선택">
                <option value="both"<?php echo get_selected($row['gr_device'], 'both'); ?>>모두</option>
                <option value="pc"<?php echo get_selected($row['gr_device'], 'pc'); ?>>PC</option>
                <option value="mobile"<?php echo get_selected($row['gr_device'], 'mobile'); ?>>모바일</option>
            </select>
        </td>
        <td class="td_smallmng"><?php echo $s_upd ?></td>
    </tr>

    <?php
        }
    if ($i == 0)
        echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </table>

    <div class="btn_list">
        <input type="submit" name="act_button" onclick="document.pressed=this.value" value="선택수정">
        <input type="submit" name="act_button" onclick="document.pressed=this.value" value="선택삭제">
        <a href="./boardgroup_form.php">게시판그룹 추가</a>
    </div>
    </form>
</section>

<?php
$pagelist = get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, $_SERVER['PHP_SELF'].'?'.$qstr.'&amp;page=');
echo $pagelist;
?>

<script>
function fboardgrouplist_submit(f)
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
</script>

<?php
include_once('./admin.tail.php');
?>
