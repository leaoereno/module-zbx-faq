-- =============================================================
-- zbx-faq v2.0.0 — Permissões de acesso (role_rule)
-- =============================================================

INSERT INTO role_rule (role_ruleid, roleid, type, name, value_int, value_str, value_moduleid)
SELECT
    (SELECT COALESCE(MAX(role_ruleid), 0) FROM role_rule) + ROW_NUMBER() OVER (ORDER BY r.roleid, a.action_name),
    r.roleid, 2, 'actions', 1, a.action_name, NULL
FROM role r
CROSS JOIN (
    SELECT 'zbx.faq.articles'              AS action_name UNION ALL
    SELECT 'zbx.faq.write'                 UNION ALL
    SELECT 'zbx.faq.write.edit'            UNION ALL
    SELECT 'zbx.faq.write.save'            UNION ALL
    SELECT 'zbx.faq.view'                  UNION ALL
    SELECT 'zbx.faq.review'                UNION ALL
    SELECT 'zbx.faq.review.edit'           UNION ALL
    SELECT 'zbx.faq.review.save'           UNION ALL
    SELECT 'zbx.faq.admin'                 UNION ALL
    SELECT 'zbx.faq.admin.category.save'   UNION ALL
    SELECT 'zbx.faq.admin.category.delete' UNION ALL
    SELECT 'zbx.faq.admin.article.delete'  UNION ALL
    SELECT 'zbx.faq.media.upload'          UNION ALL
    SELECT 'zbx.faq.media.delete'
) a
WHERE r.type IN (1, 2, 3)
  AND NOT EXISTS (
      SELECT 1 FROM role_rule rr2
      WHERE rr2.roleid=r.roleid AND rr2.value_str=a.action_name AND rr2.type=2
  );
