
SELECT method, count(*) as counts  FROM `conversations` WHERE date_created >= '2017-07-01' AND date_created <= '2018-06-30' GROUP BY method ORDER BY counts DESC;
SELECT method, count(*) as counts  FROM `conversations` WHERE date_created >= '2018-07-01' AND date_created <= '2019-06-30' GROUP BY method ORDER BY counts DESC;
SELECT method, count(*) as counts  FROM `conversations` WHERE date_created >= '2019-07-01' AND date_created <= '2020-06-30' GROUP BY method ORDER BY counts DESC;
SELECT method, count(*) as counts  FROM `conversations` WHERE date_created >= '2020-07-01' AND date_created <= '2021-06-30' GROUP BY method ORDER BY counts DESC;
SELECT method, count(*) as counts  FROM `conversations` WHERE date_created >= '2021-07-01' AND date_created <= '2022-06-30' GROUP BY method ORDER BY counts DESC;

SELECT method, status, count(*) as counts FROM `conversations` WHERE date_created >= '2017-07-01' AND date_created <= '2018-06-30' GROUP BY method, status ORDER BY method, counts DESC;
SELECT method, status, count(*) as counts FROM `conversations` WHERE date_created >= '2018-07-01' AND date_created <= '2019-06-30' GROUP BY method, status ORDER BY method, counts DESC;
SELECT method, status, count(*) as counts FROM `conversations` WHERE date_created >= '2019-07-01' AND date_created <= '2020-06-30' GROUP BY method, status ORDER BY method, counts DESC;
SELECT method, status, count(*) as counts FROM `conversations` WHERE date_created >= '2020-07-01' AND date_created <= '2021-06-30' GROUP BY method, status ORDER BY method, counts DESC;
SELECT method, status, count(*) as counts FROM `conversations` WHERE date_created >= '2021-07-01' AND date_created <= '2022-06-30' GROUP BY method, status ORDER BY method, counts DESC;

