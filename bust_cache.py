import os
import time

def bust_cache():
    # Append timestamp to CSS and JS files in PHP files
    ts = str(int(time.time()))
    for root, dirs, files in os.walk("c:/projectCoding/hr/public_html"):
        for file in files:
            if file.endswith(".php"):
                path = os.path.join(root, file)
                with open(path, "r", encoding="utf-8") as f:
                    content = f.read()

                # Add explicit cache busting strings
                content = content.replace('.css?ver=0.001', f'.css?ver={ts}')
                content = content.replace('.css?ver=0', f'.css?ver={ts}')
                content = content.replace('.js?ver=0', f'.js?ver={ts}')
                content = content.replace("src='<?php echo DIR_ROOT; ?>/assets/js/header.js'", f"src='<?php echo DIR_ROOT; ?>/assets/js/header.js?ver={ts}'")
                content = content.replace("src='<?php echo DIR_ROOT; ?>/assets/js/hr_tbl.js'", f"src='<?php echo DIR_ROOT; ?>/assets/js/hr_tbl.js?ver={ts}'")
                content = content.replace("src='<?php echo DIR_ROOT; ?>/assets/js/modal.js'", f"src='<?php echo DIR_ROOT; ?>/assets/js/modal.js?ver={ts}'")
                content = content.replace("src='<?php echo DIR_ROOT; ?>/assets/js/adjList.js'", f"src='<?php echo DIR_ROOT; ?>/assets/js/adjList.js?ver={ts}'")
                content = content.replace("href='<?php echo DIR_ROOT; ?>/assets/css/hr_tbl.css'", f"href='<?php echo DIR_ROOT; ?>/assets/css/hr_tbl.css?ver={ts}'")
                
                with open(path, "w", encoding="utf-8") as f:
                    f.write(content)

if __name__ == "__main__":
    bust_cache()
