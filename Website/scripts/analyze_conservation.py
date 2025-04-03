#!/usr/bin/env python3
import subprocess
import sys
import os
import shutil

# 硬编码输出目录
DATA_DIR = "/home/s2682415/public_html/Website/data"
PLOTCON = "/usr/bin/plotcon"

def filter_fasta(input_file, taxonomy_filter, min_length, max_length, max_count):
    """
    过滤输入 FASTA 文件：
      - taxonomy_filter：如果非空，则仅保留头部包含该关键字的序列
      - min_length、max_length：过滤序列长度（若为 None 或 0，则不做限制）
      - max_count：仅保留最多 max_count 条序列（若为 0，则不限制）
    返回过滤后的临时文件路径
    """
    filtered_file = input_file.replace(".fasta", "_filtered.fasta")
    count = 0
    previous_header = None  # 新增：记录前一个标题行
    with open(input_file, "r") as fin, open(filtered_file, "w") as fout:
        write_seq = False
        seq_lines = []
        header = ""
        for line in fin:
            line = line.rstrip('\n')  # 统一处理换行符
            
            if line.startswith(">"):
                # 新增：标题行去重逻辑
                current_header = line
                if previous_header == current_header:
                    continue  # 跳过重复标题行
                previous_header = current_header
                
                # 处理上一条序列
                if seq_lines:
                    seq = "".join(seq_lines).replace("\n", "")
                    seq_len = len(seq)
                    if (min_length and seq_len < min_length) or (max_length and seq_len > max_length):
                        pass  # 不满足长度条件，跳过
                    else:
                        fout.write(header + '\n')
                        fout.write("\n".join(seq_lines) + '\n')
                        count += 1
                        if max_count and count >= max_count:
                            break
                    seq_lines = []
                
                # 检查 taxonomy_filter
                if taxonomy_filter and taxonomy_filter.lower() not in line.lower():
                    write_seq = False
                else:
                    write_seq = True
                    header = line
            else:
                if write_seq and line.strip():  # 忽略空行
                    seq_lines.append(line)
        
        # 处理最后一条序列
        if seq_lines and (not max_count or count < max_count):
            seq = "".join(seq_lines).replace("\n", "")
            seq_len = len(seq)
            if ((min_length and seq_len >= min_length) or not min_length) and ((max_length and seq_len <= max_length) or not max_length):
                fout.write(header + '\n')
                fout.write("\n".join(seq_lines) + '\n')
    
    return filtered_file

def analyze_conservation(fasta_file, taxonomy_filter="", min_length=0, max_length=0, max_count=0):
    try:
        if not os.path.exists(fasta_file):
            print(f"❌ Error: {fasta_file} not found.")
            return

        if taxonomy_filter or min_length or max_length or max_count:
            print("\n🔍 Filtering input sequences...")
            fasta_file = filter_fasta(fasta_file, taxonomy_filter, min_length, max_length, max_count)
            print(f"✅ Filtering completed. Filtered file: {fasta_file}")

        print("\n🧬 Running conservation analysis with plotcon...")
        command = [PLOTCON, "-sequence", fasta_file, "-graph", "png", "-winsize", "4"]
        subprocess.run(command, cwd=DATA_DIR, check=True)

        generated_file = None
        for file in os.listdir(DATA_DIR):
            if file.startswith("plotcon.") and file.endswith(".png"):
                generated_file = os.path.join(DATA_DIR, file)
                break
        if not generated_file:
            print("⚠️ Error: plotcon did not generate a PNG file.")
            return

        output_file = os.path.join(DATA_DIR, os.path.basename(fasta_file).split('.')[0] + "_plotcon.png")
        shutil.move(generated_file, output_file)
        print(f"✅ Conservation analysis completed. Results saved to {output_file}")

    except subprocess.CalledProcessError as e:
        print(f"⚠️ Error running plotcon: {e}")
    except Exception as e:
        print(f"⚠️ Error processing conservation analysis: {e}")

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Usage: python analyze_conservation.py <fasta_file> [taxonomy_filter] [min_length] [max_length] [max_count]")
        sys.exit(1)

    fasta_file = sys.argv[1]
    taxonomy_filter = sys.argv[2] if len(sys.argv) > 2 else ""
    try:
        min_length = int(sys.argv[3]) if len(sys.argv) > 3 and sys.argv[3].isdigit() else 0
        max_length = int(sys.argv[4]) if len(sys.argv) > 4 and sys.argv[4].isdigit() else 0
        max_count = int(sys.argv[5]) if len(sys.argv) > 5 and sys.argv[5].isdigit() else 0
    except ValueError:
        min_length = max_length = max_count = 0

    analyze_conservation(fasta_file, taxonomy_filter, min_length, max_length, max_count)
