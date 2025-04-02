-- 创建数据库（如果尚未创建）
CREATE DATABASE IF NOT EXISTS s2682415_my_first_db;
USE s2682415_my_first_db;

-- 创建存储分析记录的表
CREATE TABLE IF NOT EXISTS analysis_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    protein_family VARCHAR(255) NOT NULL,
    taxonomy VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    fasta_file VARCHAR(255) NOT NULL,
    patterns_file VARCHAR(255) NOT NULL,
    plotcon_file VARCHAR(255) NOT NULL,
    aligned_file VARCHAR(255) NOT NULL,
    similarity_matrix VARCHAR(255) NOT NULL,
    heatmap_file VARCHAR(255) NOT NULL,
    analysis_log TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
