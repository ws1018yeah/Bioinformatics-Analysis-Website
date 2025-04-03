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
        # 动态生成输出文件名
        base = os.path.basename(fasta_file)
        root = os.path.splitext(base)[0]
        matrix_file = os.path.join(DATA_DIR, f"{root}_similarity.mat")
        heatmap_file = os.path.join(DATA_DIR, f"{root}_similarity_heatmap.png")

        print("\n🔎 Generating similarity matrix with Clustal Omega...")
        command = [CLUSTALO, "-i", fasta_file, "--distmat-out", matrix_file, "--full", "--percent-id", "--force"]
        subprocess.run(command, cwd=DATA_DIR, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL, check=True)

        # 读取矩阵文件
        matrix = []
        seq_ids = []
        with open(matrix_file, 'r') as file:
            lines = file.readlines()
            # 第一行是序列数量（跳过）
            for line in lines[1:]:  # 从第二行开始读取序列ID和数据
                parts = line.strip().split()
                if len(parts) < 2:
                    continue
                seq_ids.append(parts[0])  # 第一列为序列ID
                row = [float(x) for x in parts[1:]]  # 后续列为相似性数据
                matrix.append(row)

        if not matrix:
            print("⚠️ Error: No valid data found in similarity matrix file.")
            return

        matrix = np.array(matrix)

        # 设置图形参数
        plt.figure(figsize=(12, 10))
        plt.rcParams.update({'font.size': 8})  # 调整字体大小

        # 生成热图
        heatmap = plt.imshow(matrix, cmap="YlGnBu", aspect="auto")
        plt.colorbar(heatmap, label="Similarity (%)")

        # 设置刻度标签
        plt.xticks(np.arange(len(seq_ids)), seq_ids, rotation=45, ha='right')
        plt.yticks(np.arange(len(seq_ids)), seq_ids, rotation=0, va='center')

        # 添加标题并调整布局
        plt.title("Sequence Similarity Heatmap", pad=20)
        plt.tight_layout()  # 防止标签被截断

        # 保存文件
        plt.savefig(heatmap_file, bbox_inches='tight', dpi=300)
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