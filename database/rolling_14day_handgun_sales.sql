SELECT
  DISTINCT(s.sale_id) AS ID,
  CONCAT(p.first_name, ' ', p.last_name) AS 'Customer Name',
  CASE WHEN CAST(s.sale_time AS DATE) = CURDATE() THEN '**' 
    ELSE ''
  END AS T,
  s.sale_time AS 'Sale Date',
  i.name AS Item,
  i.item_number AS BBN,    
  sp.payment_type AS Payment

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
  i.category IN(
    'Handgun-Pistol',
    'Handgun-Revolver'
  ) AND DATE_SUB(CURDATE(), INTERVAL 14 DAY) <= s.sale_time
  AND sp.payment_type NOT IN  ('Layaway Bal', 'OOS PMT', 'Deposit')
ORDER BY
  p.last_name,
  i.name,
  s.sale_time
DESC

