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