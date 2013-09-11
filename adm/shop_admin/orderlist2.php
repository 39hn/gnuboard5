<?php
$sub_menu = '400420';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '주문통합내역';
include_once (G4_ADMIN_PATH.'/admin.head.php');

$where = " where ";
$sql_search = "";
if ($search != "")
{
    if ($sel_field != "")
    {
        $sql_search .= " $where $sel_field like '%$search%' ";
        $where = " and ";
    }

    if ($save_search != $search)
        $page = 1;
}

if ($sel_field == "")  $sel_field = "od_id";
if ($sort1 == "") $sort1 = "od_id";
if ($sort2 == "") $sort2 = "desc";

$sql_common = " from {$g4['shop_order_table']}
                $sql_search ";

// 테이블의 전체 레코드수만 얻음
$row = sql_fetch("select count(od_id) as cnt " . $sql_common);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " select *
           $sql_common
           order by $sort1 $sort2
           limit $from_record, $rows ";
$result = sql_query($sql);

$qstr1 = "sel_ca_id=$sel_ca_id&amp;sel_field=$sel_field&amp;search=$search&amp;save_search=$search";
$qstr = "$qstr1&amp;sort1=$sort1&amp;sort2=$sort2&amp;page=$page";

$listall = '';
if ($search) // 검색렬일 때만 처음 버튼을 보여줌
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">전체목록</a>';
?>

<form name="frmorderlist">
<input type="hidden" name="doc" value="<?php echo $doc; ?>">
<input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
<input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="save_search" value="<?php echo $search; ?>">
<fieldset>
    <legend>주문내역 검색</legend>
    <span>
        <?php echo $listall; ?>
        전체 주문내역 <?php echo $total_count; ?>건
    </span>

    <label for="sel_field" class="sound_only">검색대상</label>
    <select name="sel_field" id="sel_field">
        <option value="od_id" <?php echo get_selected($sel_field, 'od_id'); ?>>주문번호</option>
        <option value="mb_id" <?php echo get_selected($sel_field, 'mb_id'); ?>>회원 ID</option>
        <option value="od_name" <?php echo get_selected($sel_field, 'od_name'); ?>>주문자</option>
        <option value="od_b_name" <?php echo get_selected($sel_field, 'od_b_name'); ?>>받는분</option>
        <option value="od_deposit_name" <?php echo get_selected($sel_field, 'od_deposit_name'); ?>>입금자</option>
        <option value="od_invoice" <?php echo get_selected($sel_field, 'od_invoice'); ?>>운송장번호</option>
    </select>

    <label for="search" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="search" value="<?php echo $search; ?>" id="search" class="frm_input" autocomplete="off">
    <input type="submit" value="검색" class="btn_submit">
</fieldset>
</form>

<section id="sodr_all" class="cbox">
    <h2>주문통합내역 목록</h2>
    <p><strong>주의!</strong> 주문번호를 클릭하여 나오는 주문상세내역의 주소를 외부에서 조회가 가능한곳에 올리지 마십시오.</p>

    <div class="btn_add sort_with">
        <a href="./orderprint.php" class="btn_add_optional">주문내역출력</a>
    </div>

    <ul class="sort_odr">
        <li><a href="<?php echo title_sort("od_id", 1)."&amp;$qstr1"; ?>">주문번호<span class="sound_only"> 순 정렬</span></a></li>
        <li><a href="<?php echo title_sort("od_name")."&amp;$qstr1"; ?>">주문자<span class="sound_only"> 순 정렬</span></a></li>
        <li><a href="<?php echo title_sort("od_cart_count", 1)."&amp;$qstr1"; ?>">건수<span class="sound_only"> 순 정렬</span></a></li>
        <li><a href="<?php echo title_sort("od_cart_price", 1)."&amp;$qstr1"; ?>">주문합계<span class="sound_only"> 순 정렬</span></a></li>
        <li><a href="<?php echo title_sort("od_cancel_price", 1)."&amp;$qstr1"; ?>">주문취소<span class="sound_only"> 순 정렬</span></a></li>
        <li><a href="<?php echo title_sort("od_receipt_price")."&amp;$qstr1"; ?>">입금합계<span class="sound_only"> 순 정렬</span></a></li>
        <li><a href="<?php echo title_sort("od_misu", 1)."&amp;$qstr1"; ?>">미수금<span class="sound_only"> 순 정렬</span></a></li>
    </ul>

    <ul id="sodr_all_list">
        <?php
        for ($i=0; $row=mysql_fetch_array($result); $i++) // for 부모 시작
        {
            // 결제 수단
            $s_receipt_way = $s_br = "";
            if ($row['od_settle_case'])
            {
                $s_receipt_way = $row['od_settle_case'];
                $s_br = '+';
            }
            else
            {
                $s_receipt_way = '결제수단없음';
                $s_br = '+';
            }

            if ($row['od_receipt_point'] > 0)
                $s_receipt_way .= $s_br.'포인트';

            $od_mobile = '';
            if($row['od_mobile'])
                $od_mobile = '(M)';

            $tot_itemcount     += $row['od_cart_count'];
            $tot_orderprice    += $row['od_cart_price'];
            $tot_ordercancel   += $row['od_cancel_price'];
            $tot_receiptprice  += $row['od_receipt_price'];
            $tot_couponamount  += $row['couponamount'];
            $tot_misu          += $row['misu'];

            $uid = md5($row['od_id'].$row['od_time'].$row['od_ip']);
        ?>
        <li>
            <dl class="sodr_basic">
                <dt>주문번호</dt>
                <dd>
                    <strong>
                        <?php echo $od_mobile; ?>
                        <a href="<?php echo G4_SHOP_URL; ?>/orderinquiryview.php?od_id=<?php echo $row['od_id']; ?>&amp;uid=<?php echo $uid; ?>"><?php echo $row['od_id']; ?></a>
                    </strong>
                </dd>
                <dt>주문일시</dt>
                <dd><?php echo date('y년 m월 d일 H시 i분 s초', strtotime($row['od_time'])); ?></dd>
                <dt>건수</dt>
                <dd><?php echo $row['od_cart_count']; ?>건</dd>
            </dl>

            <dl class="sodr_person">
                <dt>주문자</dt>
                <dd>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?sort1=<?php echo $sort1;?>&amp;sort2=<?php echo $sort2; ?>&amp;sel_field=od_name&amp;search=<?php echo $row['od_name']; ?>">
                        <strong><?php echo cut_str($row['od_name'],30,""); ?></strong> (<?php if ($row['mb_id']) echo $row['mb_id']; else echo '비회원'; ?>)
                    </a>
                </dd>
                <?php if ($od_deposit_name) { ?>
                <dt>입금자</dt>
                <dd><?php echo $od_deposit_name; ?></dd>
                <?php } ?>
            </dl>

            <dl class="sodr_pay">
                <dt class="sodr_pay_1">주문합계</dt>
                <dd class="sodr_pay_1"><?php echo number_format($row['od_cart_price']); ?></dd>
                <dt class="sodr_pay_1">결제수단</dt>
                <dd class="sodr_pay_1"><?php echo $s_receipt_way; ?></dd>
                <dt class="sodr_pay_1">입금합계</dt>
                <dd class="sodr_pay_1"><?php echo number_format($row['od_receipt_price']); ?></dd>
                <dt>쿠폰사용</dt>
                <dd><?php echo number_format($row['couponamount']); ?></dd>
                <dt>주문취소</dt>
                <dd><?php echo number_format($row['od_cancel_price']); ?></dd>
                <dt>미수금</dt>
                <dd><?php echo number_format($row['misu']); ?></dd>
            </dl>
            <?php
            // 상품개별출력
            $sql2 = " select it_id, it_name
                        from {$g4['shop_cart_table']}
                        where od_id = '{$row['od_id']}'
                        group by it_id
                        order by ct_id asc ";
            $result2 = sql_query($sql2);

            for ($k=0;$row2=sql_fetch_array($result2);$k++) { // for 자식 시작
                $href = G4_SHOP_URL.'/item.php?it_id='.$row2['it_id'];
            ?>

            <div class="sodr_itname">
                <a href="<?php echo $href; ?>" target="_blank"><?php echo get_it_image($row2['it_id'], 50, 50); ?> <strong><?php echo cut_str($row2['it_name'],35); ?></strong><span class="sound_only"> 새창</span></a>
            </div>

            <table>
            <thead>
            <tr>
                <th scope="col">옵션</th>
                <th scope="col">판매가</th>
                <th scope="col">수량</th>
                <th scope="col">포인트</th>
                <th scope="col">소계</th>
                <th scope="col">배송비</th>
                <th scope="col">상태</th>
            </tr>
            </thead>
            <tbody>

            <?php
                // 옵션항목
                $sql3 = " select *
                            from {$g4['shop_cart_table']}
                            where od_id = '{$row['od_id']}'
                              and it_id = '{$row2['it_id']}'
                            order by io_type asc, ct_id asc ";
                $result3 = sql_query($sql3);

                for($j=0;$row3=sql_fetch_array($result3);$j++) { // for 손자 시작
                    if($row3['io_type'])
                        $ct_price = $row3['io_price'];
                    else
                        $ct_price = ($row3['ct_price'] + $row3['io_price']);

                    $sub_price = $row3['ct_qty'] * $ct_price;
                    $sub_point = $row3['ct_qty'] * $row3['ct_point'];
                    $ct_send_cost = ($row3['ct_send_cost'] ? '착불' : '선불');
            ?>

            <tr>
                <td><?php echo $row3['ct_option']; ?></td>
                <td class="td_bignum"><?php echo number_format($ct_price); ?></td>
                <td class="td_num"><?php echo $row3['ct_qty']; ?></td>
                <td class="td_num"><?php echo number_format($sub_point); ?></td>
                <td class="td_bignum"><?php echo number_format($sub_price); ?></td>
                <td class="td_sendcost_by"><?php echo $ct_send_cost; ?></td>
                <td class="td_smallmng"><?php echo $row3['ct_status']; ?></td>
            </tr>

            <?php } // for 손자 끝 ?>

            </tbody>
            </table>

           <?php
           } // for 자식 끝
       ?>

            <div class="sodr_mng">
                <a href="./orderform.php?od_id=<?php echo $row['od_id']; ?>&amp;<?php echo $qstr; ?>">주문 수정</a>
                |
                <a href="./orderdelete.php?od_id=<?php echo $row['od_id']; ?>&amp;mb_id=<?php echo $row['mb_id']; ?>&amp;<?php echo $qstr; ?>&amp;list=2" onclick="return delete_confirm();">주문 삭제</a>
            </div>

       </li>

       <?php
       } // for 부모 끝

        if ($i == 0)
            echo '<li class="sodr_empty">자료가 한건도 없습니다.</li>';
        ?>
    </ul>

</section>

<section id="sodr_total" class="cbox">
    <h2>합계</h2>
    <p>현재 <?php echo $page;?>페이지의 주문내역 합계입니다.</p>

    <table>
    <thead>
    <tr>
        <th scope="col">주문건수</th>
        <th scope="col">주문액</th>
        <th scope="col">쿠폰</th>
        <th scope="col">취소</th>
        <th scope="col">입금완료</th>
        <th scope="col">미수금</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><?php echo (int)$tot_itemcount; ?>건</td>
        <td><?php echo number_format($tot_orderamount); ?></td>
        <td><?php echo number_format($tot_couponamount); ?></td>
        <td><?php echo number_format($tot_ordercancel); ?></td>
        <td><?php echo number_format($tot_receiptamount); ?></td>
        <td><?php echo number_format($tot_misu); ?></td>
    </tr>
    </tbody>
    </table>
</section>

<?php echo get_paging(G4_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page="); ?>

<?php
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
