SELECT
  DISTINCT(s.sale_id) AS ID,
  LEFT(CONCAT(p.first_name, ' ', p.last_name),25) AS 'Customer Name',
  CASE WHEN CAST(s.sale_time AS DATE) = CURDATE() THEN '*'
    ELSE ''
  END AS T,
  DATE_FORMAT(s.sale_time,'%m/%d/%y %H:%i') AS 'Sale Date',
  i.name AS Item,
  i.item_number AS BBN,
  CASE sp.payment_type
    WHEN 'Credit Card' THEN 'CC'
    WHEN 'Debit Card' THEN 'DC'
    WHEN 'Cash' THEN 'CA'
    WHEN 'Deposit' THEN 'DE'
    WHEN 'Layaway Bal' THEN 'LB'
    WHEN 'OOS PMT' THEN 'OP'
    WHEN 'Check' THEN 'CH'
    WHEN 'House Account' THEN 'HA'
    ELSE sp.payment_type
  END AS Pay,
  CASE i.category
    WHEN 'Rifle' THEN 'RI'
    WHEN 'Shotguns' THEN 'SH'
    WHEN 'Transfers' THEN 'TR'
    WHEN 'Longgun-Rifle' THEN 'LR'
    WHEN 'Longgun-Shotgun' THEN 'LS'
    WHEN 'Handgun-Revolver' THEN 'HR'
    WHEN 'Handgun-Pistol' THEN 'HP'
    WHEN 'Lay-A-WayPayment' THEN 'LA'
    WHEN 'SpecialOrderDeposit' THEN 'SO'
    WHEN 'Other-Receiver' THEN 'OR'
    WHEN 'Other-Frame' THEN 'OF'
    WHEN 'Other-Firearm' THEN 'OM'
    WHEN 'Longgun-Combo R/S' THEN 'LC'
    WHEN 'Other-PG Shotguns' THEN 'OS'
    WHEN 'Returns' THEN 'RE'
    ELSE i.category
  END AS Cat
FROM
  ospos_sales AS s
JOIN
  ospos_sales_items AS si
ON
  s.sale_id = si.sale_id
JOIN
  ospos_items AS i
ON
  si.item_id = i.item_id
JOIN
  ospos_people AS p
ON
  s.customer_id = p.person_id
JOIN
  ospos_sales_payments AS sp
ON
  s.sale_id = sp.sale_id

WHERE
upper(p.last_name) <> 'TILL'
AND DATE_SUB(CURDATE(), INTERVAL 10 DAY) <= s.sale_time
AND si.quantity_purchased > 0
AND i.name <> 'Lay-A-Way Payment'
AND si.item_unit_price > 0
AND i.category IN(
'Rifle',
'Shotguns',
'Longgun-Rifle',
'Longgun-Shotgun',
'Handgun-Revolver',
'Handgun-Pistol',
'Lay-A-WayPayment',
'Other-Receiver',
'Other-Frame',
'Other-Firearm',
'Longgun-Combo R/S',
'Other-PG Shotguns'
)

ORDER BY
  p.last_name,
  i.name,
  s.sale_time
DESC


