<?php
// config2.php：专门用于分析流程的配置

// 指定 Python 解释器路径（若系统中 python3 在 PATH 中，可直接使用 "python3"）
$PYTHON_EXE = "python3";

// 指定 combined.py 脚本的完整路径
// __DIR__ 表示当前文件所在的目录，即 /home/s2682415/public_html/Website/
$PYTHON_SCRIPT = __DIR__ . "/scripts/combined.py";

$PYTHON_SCRIPTS = [
    'analyze_conservation' => __DIR__ . "/scripts/analyze_conservation.py",
    'pattern_scan' => __DIR__ . "/scripts/pattern_scan.py",
    'multiple_sequence_alignment' => __DIR__ . "/scripts/multiple_sequence_alignment.py",
    'generate_similarity_matrix' => __DIR__ . "/scripts/generate_similarity_matrix.py",
    
];


?>