import os

def fix_corrupted_js(dir_path):
    for root, dirs, files in os.walk(dir_path):
        for file in files:
            if file.endswith(".js"):
                path = os.path.join(root, file)
                try:
                    with open(path, "r", encoding="utf-8") as f:
                        content = f.read()

                    # replacements
                    content = content.replace("psnlKey.value", "API_TOKEN")
                    content = content.replace("document.getElementById('psnlKey').value", "API_TOKEN")
                    content = content.replace('document.getElementById("psnlKey").value', 'API_TOKEN')
                    content = content.replace("psnlKey", "API_TOKEN")
                    content = content.replace("debugger;", "//debugger;")
                    
                    with open(path, "w", encoding="utf-8") as f:
                        f.write(content)
                except Exception as e:
                    print(f"Failed to process {path}: {e}")

if __name__ == "__main__":
    fix_corrupted_js("c:/projectCoding/hr/public_html/assets/js")
