import os
import re
from collections import Counter

def find_duplicates(dir_path):
    for root, dirs, files in os.walk(dir_path):
        for file in files:
            if file.endswith(".php"):
                path = os.path.join(root, file)
                with open(path, "r", encoding="utf-8", errors="ignore") as f:
                    content = f.read()
                    ids = re.findall(r'id="([^"]+)"', content)
                    counts = Counter(ids)
                    dups = {k: v for k, v in counts.items() if v > 1}
                    if dups:
                        print(f"File: {path}")
                        for k, v in dups.items():
                            print(f"  Duplicate ID '{k}' count {v}")

if __name__ == "__main__":
    find_duplicates("c:/projectCoding/hr/public_html")
