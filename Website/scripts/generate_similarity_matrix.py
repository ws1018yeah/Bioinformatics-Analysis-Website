#!/usr/bin/env python3
import subprocess
import sys
import os
import numpy as np
import matplotlib.pyplot as plt

# 硬编码输出目录
DATA_DIR = "/home/s2682415/public_html/Website/data"
CLUSTALO = "/usr/bin/clustalo"

def generate_similarity_matrix(fasta_file):
    try:
        #matrix_file = fasta_file.replace(".fasta", "_similarity.mat")
        #heatmap_file = fasta_file.replace(".fasta", "_similarity_heatmap.png")

        # 修改为动态路径（与PHP一致）
        base = os.path.basename(fasta_file)
        root = os.path.splitext(base)[0]
        matrix_file = os.path.join(DATA_DIR, f"{root}_similarity.mat")
        heatmap_file = os.path.join(DATA_DIR, f"{root}_similarity_heatmap.png")

        print("\n🔎 Generating similarity matrix with Clustal Omega...")
        command = [CLUSTALO, "-i", fasta_file, "--distmat-out", matrix_file, "--full", "--percent-id", "--force"]
        subprocess.run(command, cwd=DATA_DIR, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL, check=True)

        # 读取矩阵文件，跳过第一行和第一列
        matrix = []
        with open(matrix_file, 'r') as file:
            lines = file.readlines()
            for line in lines[1:]:  # 跳过第一行
                parts = line.strip().split()
                if len(parts) < 2:
                    continue
                row = [float(x) for x in parts[1:]]  # 跳过第一列（序列 ID）
                matrix.append(row)

        if not matrix:
            print("⚠️ Error: No valid data found in similarity matrix file.")
            return

        matrix = np.array(matrix)

        plt.figure(figsize=(8, 6))
        plt.imshow(matrix, cmap="YlGnBu", aspect="auto")
        plt.colorbar(label="Similarity (%)")
        plt.title("Sequence Similarity Heatmap")
        plt.xlabel("Sequence")
        plt.ylabel("Sequence")
        plt.xticks([])  
        plt.yticks([])
        plt.savefig(heatmap_file)
        plt.close()
        print(f"✅ Similarity matrix and heatmap saved to {matrix_file} and {heatmap_file}")

    except subprocess.CalledProcessError as e:
        print(f"⚠️ Error generating similarity matrix: {e}")
    except Exception as e:
        print(f"⚠️ Error processing similarity matrix: {e}")

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python generate_similarity_matrix.py <fasta_file>")
        sys.exit(1)

    fasta_file = sys.argv[1]
    generate_similarity_matrix(fasta_file)