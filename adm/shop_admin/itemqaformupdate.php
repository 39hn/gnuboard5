<?php
$sub_menu = '400660';
include_once('./_common.php');

check_demo();

if ($w == 'd')
    auth_check($auth[$sub_menu], "d");
else
    auth_check($auth[$sub_menu], "w");

$qstr = "page=$page&amp;sort1=$sort1&amp;sort2=$sort2";

if ($w == "u") 
{
    $sql = "update {$g5['shop_item_qa_table']}
               set iq_subject = '$iq_subject',
                   iq_question = '$iq_question',
                   iq_answer = '$iq_answer'
             where iq_id = '$iq_id' ";
    sql_query($sql);

    goto_url("./itemqaform.php?w=$w&amp;iq_id=$iq_id&amp;$qstr");
} 
else {
    alert();
}
?>
