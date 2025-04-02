#!/usr/bin/env python3

import subprocess
import sys
import os
from Bio import Entrez
import numpy as np
import matplotlib.pyplot as plt

# 硬编码输出目录
DATA_DIR = "/home/s2682415/public_html/Website/data"

# 请根据实际情况修改 EMBOSS 工具和 Clustal Omega 的完整路径
PLOTCON = "/usr/bin/plotcon"
PATMAT = "/usr/bin/patmatmotifs"
CLUSTALO = "/usr/bin/clustalo" 

def fetch_protein_sequences(family, taxonomy, email):
    """Fetch protein sequences from NCBI using Entrez."""
    # 将空格替换为下划线，确保一致性
    family = family.replace(" ", "_")
    taxonomy = taxonomy.replace(" ", "_")
    
    Entrez.email = email
    search_term = f"{family}[Protein Name] AND {taxonomy}[Organism]"
    print(f"\n🔍 Searching for: {search_term}")

    try:
        # 搜索符合条件的序列
        handle = Entrez.esearch(db="protein", term=search_term, retmax=10)
        record = Entrez.read(handle)
        handle.close()
        id_list = record["IdList"]

        if not id_list:
            print("❌ No sequences found.")
            return None

        # 获取序列信息
        handle = Entrez.efetch(db="protein", id=",".join(id_list), rettype="fasta", retmode="text")
        sequences = handle.read()
        handle.close()

        # 确保输出目录存在
        os.makedirs(DATA_DIR, exist_ok=True)
        output_file = os.path.join(DATA_DIR, f"{family}_{taxonomy}.fasta")
        with open(output_file, "w") as file:
            file.write(sequences)

        print(f"✅ Sequences saved to {output_file}")
        return output_file

    except Exception as e:
        print(f"⚠️ Error fetching sequences: {e}")
        return None

def analyze_conservation(fasta_file):
    """Perform conservation analysis using EMBOSS plotcon."""
    try:
        if not os.path.exists(fasta_file):
            print(f"❌ Error: {fasta_file} not found.")
            return

        print("\n🧬 Running conservation analysis with plotcon...")
        command = [PLOTCON, "-sequence", fasta_file, "-graph", "png", "-winsize", "4"]
        subprocess.run(command, cwd=DATA_DIR, check=True)

        # 自动检测生成的 PNG 文件
        for file in os.listdir(DATA_DIR):
            if file.startswith("plotcon.") and file.endswith(".png"):
                generated_file = os.path.join(DATA_DIR, file)
                break
        else:
            print("⚠️ Error: plotcon did not generate a PNG file.")
            return

        # 目标输出文件名（和输入 fasta 文件关联）
        output_file = fasta_file.replace(".fasta", "_plotcon.png")
        os.rename(generated_file, output_file)
        print(f"✅ Conservation analysis completed. Results saved to {output_file}")

    except subprocess.CalledProcessError as e:
        print(f"⚠️ Error running plotcon: {e}")

def pattern_scan(fasta_file):
    """Scan protein sequences for motifs using EMBOSS patmatmotifs."""
    try:
        if not os.path.exists(fasta_file):
            print(f"❌ Error: {fasta_file} not found.")
            return

        output_file = fasta_file.replace(".fasta", "_patterns.txt")
        print("\n🔎 Running motif scan with patmatmotifs...")
        command = [PATMAT, "-sequence", fasta_file, "-outfile", output_file]
        subprocess.run(command, cwd=DATA_DIR, check=True)
        print(f"✅ Motif scan completed. Results saved to {output_file}")

    except subprocess.CalledProcessError as e:
        print(f"⚠️ Error running patmatmotifs: {e}")

def multiple_sequence_alignment(fasta_file):
    """Perform multiple sequence alignment using Clustal Omega."""
    try:
        alignment_file = fasta_file.replace(".fasta", "_aligned.aln")
        print("\n🔎 Running multiple sequence alignment with Clustal Omega...")
        command = [CLUSTALO, "-i", fasta_file, "-o", alignment_file, "--outfmt", "clu"]
        subprocess.run(command, cwd=DATA_DIR, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL, check=True)
        print(f"✅ Alignment completed. Results saved to {alignment_file}")
        return alignment_file

    except subprocess.CalledProcessError as e:
        print(f"⚠️ Error running Clustal Omega: {e}")
        return None

def generate_similarity_matrix(fasta_file):
    """Generate similarity matrix using Clustal Omega and visualize as heatmap using matplotlib."""
    try:
        matrix_file = fasta_file.replace(".fasta", "_similarity.mat")
        heatmap_file = fasta_file.replace(".fasta", "_similarity_heatmap.png")

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
        # 可以根据需要调整坐标轴刻度
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
    if len(sys.argv) != 4:
        print("Usage: python combined.py <protein_family> <taxonomy> <email>")
        sys.exit(1)

    protein_family = sys.argv[1]
    taxonomy = sys.argv[2]
    email = sys.argv[3]

    print("\n🚀 Starting analysis pipeline...")

    fasta_file = fetch_protein_sequences(protein_family, taxonomy, email)
    if fasta_file:
        analyze_conservation(fasta_file)
        pattern_scan(fasta_file)
        aligned_file = multiple_sequence_alignment(fasta_file)
        if aligned_file:
            generate_similarity_matrix(fasta_file)

    print("\n🎉 Analysis pipeline completed!")
