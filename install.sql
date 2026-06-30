-- =============================================================
-- zbx-faq v2.0.0 — DDL
-- Banco: MariaDB 10.11 / Zabbix 7.0 LTS
-- =============================================================

CREATE TABLE IF NOT EXISTS zbx_faq_category (
    categoryid    BIGINT UNSIGNED NOT NULL,
    parent_id     BIGINT UNSIGNED NULL DEFAULT NULL,
    name          VARCHAR(255)    NOT NULL,
    description   TEXT            NULL,
    sort_order    INT             NOT NULL DEFAULT 0,
    created_by    BIGINT UNSIGNED NOT NULL,
    created_at    INT UNSIGNED    NOT NULL,
    PRIMARY KEY (categoryid),
    KEY idx_faq_cat_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- status: 0=rascunho, 1=em revisão, 2=publicado, 3=arquivado, 4=rejeitado
-- content_type: 0=texto puro, 1=markdown, 2=html
CREATE TABLE IF NOT EXISTS zbx_faq_article (
    articleid      BIGINT UNSIGNED NOT NULL,
    categoryid     BIGINT UNSIGNED NOT NULL,
    title          VARCHAR(500)    NOT NULL,
    content        LONGTEXT        NOT NULL,
    content_type   TINYINT         NOT NULL DEFAULT 1,
    status         TINYINT         NOT NULL DEFAULT 0,
    review_comment TEXT            NULL,
    created_by     BIGINT UNSIGNED NOT NULL,
    created_at     INT UNSIGNED    NOT NULL,
    updated_by     BIGINT UNSIGNED NOT NULL,
    updated_at     INT UNSIGNED    NOT NULL,
    published_by   BIGINT UNSIGNED NULL DEFAULT NULL,
    published_at   INT UNSIGNED    NULL DEFAULT NULL,
    PRIMARY KEY (articleid),
    KEY idx_faq_art_category   (categoryid),
    KEY idx_faq_art_status     (status),
    KEY idx_faq_art_created_by (created_by),
    KEY idx_faq_art_created    (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS zbx_faq_tag (
    tagid  BIGINT UNSIGNED NOT NULL,
    name   VARCHAR(100)    NOT NULL,
    PRIMARY KEY (tagid),
    UNIQUE KEY uq_faq_tag_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS zbx_faq_article_tag (
    article_tagid BIGINT UNSIGNED NOT NULL,
    articleid     BIGINT UNSIGNED NOT NULL,
    tagid         BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (article_tagid),
    UNIQUE KEY uq_faq_art_tag (articleid, tagid),
    KEY idx_faq_art_tag_tag (tagid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Segmentação: grupos que podem ver/editar cada artigo
CREATE TABLE IF NOT EXISTS zbx_faq_article_group (
    article_groupid BIGINT UNSIGNED NOT NULL,
    articleid       BIGINT UNSIGNED NOT NULL,
    usrgrpid        BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (article_groupid),
    UNIQUE KEY uq_faq_art_grp (articleid, usrgrpid),
    KEY idx_faq_art_grp_grp (usrgrpid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS zbx_faq_media (
    mediaid       BIGINT UNSIGNED NOT NULL,
    articleid     BIGINT UNSIGNED NOT NULL,
    filename      VARCHAR(255)    NOT NULL,
    original_name VARCHAR(255)    NOT NULL,
    mime_type     VARCHAR(100)    NOT NULL,
    file_size     INT UNSIGNED    NOT NULL DEFAULT 0,
    uploaded_by   BIGINT UNSIGNED NOT NULL,
    uploaded_at   INT UNSIGNED    NOT NULL,
    PRIMARY KEY (mediaid),
    KEY idx_faq_media_article (articleid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Histórico de revisões
CREATE TABLE IF NOT EXISTS zbx_faq_revision (
    revisionid    BIGINT UNSIGNED NOT NULL,
    articleid     BIGINT UNSIGNED NOT NULL,
    title         VARCHAR(500)    NOT NULL,
    content       LONGTEXT        NOT NULL,
    content_type  TINYINT         NOT NULL DEFAULT 1,
    status_from   TINYINT         NOT NULL,
    status_to     TINYINT         NOT NULL,
    changed_by    BIGINT UNSIGNED NOT NULL,
    changed_at    INT UNSIGNED    NOT NULL,
    note          VARCHAR(500)    NULL,
    PRIMARY KEY (revisionid),
    KEY idx_faq_rev_article (articleid),
    KEY idx_faq_rev_date    (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Migração: adiciona coluna review_comment se já existir a tabela
ALTER TABLE zbx_faq_article ADD COLUMN IF NOT EXISTS review_comment TEXT NULL AFTER published_at;
