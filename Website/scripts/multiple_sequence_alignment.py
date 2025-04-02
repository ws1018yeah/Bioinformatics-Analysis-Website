#!/usr/bin/env python3
import subprocess
import sys
import os

# 硬编码输出目录
DATA_DIR = "/home/s2682415/public_html/Website/data"
CLUSTALO = "/usr/bin/clustalo"

def multiple_sequence_alignment(fasta_file):
    try:
        # 提取文件名并构造输出路径
        base = os.path.basename(fasta_file)
        root, ext = os.path.splitext(base)
        aligned_name = f"{root}_aligned.aln"
        alignment_file = os.path.join(DATA_DIR, aligned_name)
        
        print("\n🔎 Running multiple sequence alignment with Clustal Omega...")
        command = [CLUSTALO, "-i", fasta_file, "-o", alignment_file, "--outfmt", "clu"]
        subprocess.run(command, check=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        print(f"✅ Alignment completed. Results saved to {alignment_file}")
        return alignment_file
    except subprocess.CalledProcessError as e:
        print(f"⚠️ Error running Clustal Omega: {e}")
        return None

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python multiple_sequence_alignment.py <fasta_file>")
        sys.exit(1)

    fasta_file = sys.argv[1]
    multiple_sequence_alignment(fasta_file)