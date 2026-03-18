import os
import re

directories = ['resources/views', 'resources/js']

for d in directories:
    for root, dirs, files in os.walk(d):
        for file in files:
            if file.endswith('.blade.php') or file.endswith('.js') or file.endswith('.vue'):
                path = os.path.join(root, file)
                with open(path, 'r') as f:
                    content = f.read()

                new_content = re.sub(r'\blime(-[0-9]{2,3})\b', r'olive\1', content)

                if new_content != content:
                    with open(path, 'w') as f:
                        f.write(new_content)
                    print(f"Updated {path}")
