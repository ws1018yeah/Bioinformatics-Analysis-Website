#!/usr/bin/env python3
import subprocess
import sys
import os
import shutil

# 硬编码输出目录
DATA_DIR = "/home/s2682415/public_html/Website/data"
PLOTCON = "/usr/bin/plotcon"

def analyze_conservation(fasta_file):
    try:
        if not os.path.exists(fasta_file):
            print(f"❌ Error: {fasta_file} not found.")
            return

        print("\n🧬 Running conservation analysis with plotcon...")
        command = [PLOTCON, "-sequence", fasta_file, "-graph", "png", "-winsize", "4"]
        subprocess.run(command, cwd=DATA_DIR, check=True)

        # 自动检测生成的 PNG 文件
        generated_file = None
        for file in os.listdir(DATA_DIR):
            if file.startswith("plotcon.") and file.endswith(".png"):
                generated_file = os.path.join(DATA_DIR, file)
                break
        if not generated_file:
            print("⚠️ Error: plotcon did not generate a PNG file.")
            return

        # 目标输出文件名（和输入 fasta 文件关联）
        #output_file = os.path.join(DATA_DIR, os.path.basename(fasta_file).replace(".fasta", "_plotcon.png"))
        output_file = os.path.join(DATA_DIR, os.path.basename(fasta_file).split('.')[0] + "_plotcon.png")


        # 使用 shutil.move 替代 os.rename 以支持跨设备移动
        shutil.move(generated_file, output_file)
        print(f"✅ Conservation analysis completed. Results saved to {output_file}")

    except subprocess.CalledProcessError as e:
        print(f"⚠️ Error running plotcon: {e}")
    except Exception as e:
        print(f"⚠️ Error processing conservation analysis: {e}")

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python analyze_conservation.py <fasta_file>")
        sys.exit(1)

    fasta_file = sys.argv[1]
    analyze_conservation(fasta_file)