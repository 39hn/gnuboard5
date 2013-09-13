<?php
$sub_menu = '400750';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$addr = trim($_GET['addr']);
$no = trim($_GET['no']);

if($addr) {
    $option_list = '';
    $zipfile = array();
    $fp = fopen(G5_BBS_PATH."/zip.db", "r");
    while(!feof($fp)) {
        $zipfile[] = fgets($fp, 4096);
    }
    fclose($fp);

    $search_count = 0;

    while ($zipcode = each($zipfile))
    {
        if(strstr(substr($zipcode[1],8,512), $addr))
        {
            $address = trim($zipcode[1]);

            $list[$search_count] = $address;

            $search_count++;
        }
    }

    if($search_count) {
        natsort($list);
        if($no == 2)
            $list = array_reverse($list);

        $result = array();

        foreach($list as $value) {
            $code = substr($value, 0, 7);
            $result[] = '<input type="hidden" name="code[]" value="'.$code.'">'.$value.' <button type="button" class="select_btn btn_frmline">선택</button>'.PHP_EOL;
        }
    }
}

$g5['title'] = "우편번호 찾기";
include_once(G5_PATH.'/head.sub.php');
?>


<div id="sendcost_postal_win" class="new_win">
    <h1 id="new_win_title"><?php echo $g5['title']; ?></h1>


    <form name="fzipcode" id="fzipcode" method="get">
    <input type="hidden" name="no" value="<?php echo $no; ?>">

    <p class="new_win_desc">주소지의 시/군을 입력하세요.</p>

    <fieldset>
        <label for="addr">주소</label>
        <input type="text" name="addr" value="<?php echo stripslashes($addr); ?>" id="addr" class="frm_input" size="20">
        <input type="submit" value=" 검색 " class="btn_frmline">
    </fieldset>

    </form>

    <?php if($search_count) { ?>
    <strong class="new_win_desc">검색결과</strong>
    <ul>
        <?php
        for($i=0; $i<$search_count; $i++) {
        ?>
        <li><?php echo $result[$i]; ?></li>
        <?php } ?>
    </ul>
    <?php } else { ?>
    <p class="new_win_desc">검색된 결과가 없습니다.</p>
    <?php } ?>

    <div class="btn_win">
        <a href="javascript:window.close();">창닫기</a>
    </div>
</div>

<script>
$(function() {
    $("#fzipcode").submit(function() {
        if($.trim($("input[name=addr]").val()) == "") {
            alert("주소를 입력해 주십시오.");
            return false;
        }

        return true;
    });

    $(".select_btn").click(function() {
        var code = $(this).closest("li").find("input[name='code[]']").val();
        var $opener = window.opener;

        $opener.$("input[name=sc_zip<?php echo $no; ?>]").val(code);
        window.close();
    });
});
</script>

<?php
include_once(G5_PATH."/tail.sub.php");
?>