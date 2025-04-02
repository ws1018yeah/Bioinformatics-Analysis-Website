#!/usr/bin/env python3
import subprocess
import sys
import os

# 硬编码输出目录
DATA_DIR = "/home/s2682415/public_html/Website/data"
PATMAT = "/usr/bin/patmatmotifs"

def pattern_scan(fasta_file):
    try:
        if not os.path.exists(fasta_file):
            print(f"❌ Error: {fasta_file} not found.")
            return

        # 确保输出文件保存在DATA_DIR目录下
        output_file = os.path.join(DATA_DIR, os.path.basename(fasta_file).replace(".fasta", "_patterns.txt"))
        print("\n🔎 Running motif scan with patmatmotifs...")
        command = [PATMAT, "-sequence", fasta_file, "-outfile", output_file]
        subprocess.run(command, cwd=DATA_DIR, check=True)
        print(f"✅ Motif scan completed. Results saved to {output_file}")

    except subprocess.CalledProcessError as e:
        print(f"⚠️ Error running patmatmotifs: {e}")

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python pattern_scan.py <fasta_file>")
        sys.exit(1)

    fasta_file = sys.argv[1]
    pattern_scan(fasta_file)