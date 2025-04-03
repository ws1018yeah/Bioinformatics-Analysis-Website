<?php 
// config2.php: Configuration specifically for the analysis process

// Specify the path to the Python interpreter (if python3 is in PATH, "python3" can be used directly)
$PYTHON_EXE = "python3";

// Specify the full path to the combined.py script
// __DIR__ represents the directory where the current file is located, i.e., /home/s2682415/public_html/Website/
$PYTHON_SCRIPT = __DIR__ . "/scripts/combined.py";

$PYTHON_SCRIPTS = [
    'analyze_conservation' => __DIR__ . "/scripts/analyze_conservation.py",
    'pattern_scan' => __DIR__ . "/scripts/pattern_scan.py",
    'multiple_sequence_alignment' => __DIR__ . "/scripts/multiple_sequence_alignment.py",
    'generate_similarity_matrix' => __DIR__ . "/scripts/generate_similarity_matrix.py",
];
?>
