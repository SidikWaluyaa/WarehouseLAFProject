import os
import re

views_dir = 'resources/views'

for root, dirs, files in os.walk(views_dir):
    for f in files:
        if f.endswith('.blade.php'):
            path = os.path.join(root, f)
            try:
                content = open(path, encoding='utf-8').read()
                for match in re.finditer(r'<input\s+([^>]+)>', content):
                    input_attrs = match.group(1)
                    if 'wire:model' in input_attrs:
                        print(f"{f}:")
                        print(f"   Line context: <input {input_attrs.strip()}>")
            except Exception as e:
                pass
