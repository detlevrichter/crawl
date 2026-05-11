ALTER TABLE `offer_competencies` DROP `category_id`;
ALTER TABLE `competency_types` DROP `query_value`;
DROP TABLE `offer_categories`;
ALTER TABLE competency_types ADD UNIQUE KEY uq_competency_types_slug (slug);


CREATE TABLE category_competency_type (    
    category_slug VARCHAR(255) NOT NULL,   
 competency_type_slug VARCHAR(255) NOT NULL,    
 PRIMARY KEY (category_slug, competency_type_slug),   
  KEY idx_competency_type_slug (competency_type_slug),   
   CONSTRAINT fk_cct_category
 FOREIGN KEY (category_slug)
 REFERENCES categories (slug)
 ON DELETE CASCADE
 ON UPDATE CASCADE,    CONSTRAINT fk_cct_competency_type
 FOREIGN KEY (competency_type_slug)
 REFERENCES competency_types (slug)
 ON DELETE CASCADE
 ON UPDATE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



create VIEW wp_category_competency_type as SELECT * FROM `category_competency_type`;

CREATE TABLE `categories_crawl_master` (
  `categories_slug` varchar(255) NOT NULL,
  `crawl_master_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
--
-- Indizes für die Tabelle `categories_crawl_master`
--
ALTER TABLE `categories_crawl_master`
  ADD PRIMARY KEY (`categories_slug`,`crawl_master_id`),
  ADD KEY `fk_ccm_crawl` (`crawl_master_id`);
--
-- Constraints der Tabelle `categories_crawl_master`
--
ALTER TABLE `categories_crawl_master`
  ADD CONSTRAINT `fk_ccm_category` FOREIGN KEY (`categories_slug`) REFERENCES `categories` (`slug`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ccm_crawl` FOREIGN KEY (`crawl_master_id`) REFERENCES `crawl_master` (`id`) ON DELETE CASCADE;


ALTER TABLE `crawl_master` ADD `Name` VARCHAR(255) NOT NULL AFTER `id`; 

CREATE TABLE crawl_master_competency_types (
    master_id INT(11) NOT NULL,
    competency_slug VARCHAR(255) NOT NULL,

    PRIMARY KEY (master_id, competency_slug),

    KEY idx_competency_slug (competency_slug),

    CONSTRAINT fk_cmct_master
        FOREIGN KEY (master_id)
        REFERENCES crawl_master (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_cmct_competency
        FOREIGN KEY (competency_slug)
        REFERENCES competency_types (slug)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;