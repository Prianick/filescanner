create temp table users(id bigserial, group_id bigint);
insert into users(group_id) values (1), (1), (1), (2), (1), (3);

SELECT min(id) as min_id, group_id, count(*) as count FROM 
(SELECT id as sub_id, id - row_number() OVER (PARTITION BY group_id ORDER BY id) AS group_num, * 
FROM users) sub_tab 
GROUP BY group_id, group_num
ORDER BY min_id;
