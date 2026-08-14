import json
import re
import os

transcript_path = r"C:\Users\LENOVO\.gemini\antigravity\brain\e9080a23-ac74-471e-a612-3e092a574720\.system_generated\logs\transcript_full.jsonl"
target = "index.blade.php"

content_blocks = []
with open(transcript_path, 'r', encoding='utf-8') as f:
    for line in f:
        try:
            data = json.loads(line)
            if data.get("type") == "TOOL_RESPONSE":
                content = data.get("content", "")
                if target in content and "Total Lines:" in content:
                    lines_match = re.search(r'Showing lines (\d+) to (\d+)', content)
                    if lines_match:
                        block = {}
                        code_lines = content.split('\n')
                        for code_line in code_lines:
                            line_match = re.match(r'^(\d+):\s(.*)$', code_line)
                            if line_match:
                                line_num = int(line_match.group(1))
                                line_text = line_match.group(2)
                                block[line_num] = line_text
                        if block:
                            content_blocks.append(block)
        except Exception as e:
            pass

print(f"Found {len(content_blocks)} view_file blocks")

# Merge them sequentially
latest_content = {}
for block in content_blocks:
    for num, text in block.items():
        latest_content[num] = text

with open('reconstructed_index.txt', 'w', encoding='utf-8') as out:
    for i in sorted(latest_content.keys()):
        out.write(f"{latest_content[i]}\n")
print(f"Reconstructed {len(latest_content)} lines.")
