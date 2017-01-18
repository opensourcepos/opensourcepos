SELECT
  DISTINCT(s.sale_id) AS ID,
  LEFT(CONCAT(p.first_name, ' ', p.last_name),25) AS 'Customer Name',
  CASE WHEN CAST(s.sale_time AS DATE) = CURDATE() THEN '*'
    ELSE ''
  END AS T,
  DATE_FORMAT(s.sale_time,'%m/%d/%y %H:%i') AS 'Sale Date',
  CASE i.name
     WHEN 'Release Date' THEN CONCAT(i.name, ' ', si.description)
     WHEN 'Final Lay-A-Way Payment' THEN CONCAT(i.name, ' ', si.description)
    ELSE i.name
  END AS 'Item Name',
  CASE i.item_number
    WHEN 'Final Lay-A-Way Payment' THEN 'F-LAW'
    ELSE i.item_number
  END AS 'Item#',
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
    WHEN 'Handgun-Revolver' THEN 'HR'
    WHEN 'Handgun-Pistol' THEN 'HP'
    WHEN 'Lay-A-WayPayment' THEN 'LA'
    WHEN 'Other-Firearm' THEN 'OM'
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
AND i.category IN(
'Handgun-Revolver',
'Handgun-Pistol',
'Lay-A-WayPayment',
'Other-Firearm'
)

ORDER BY
  p.last_name,
  i.name,
  s.sale_time
DESC

