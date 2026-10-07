import os
import re
import sys

LANG_EN_FILE = 'lang/en.php'
LANG_MS_FILE = 'lang/ms.php'

def get_existing_strings(filepath):
    if not os.path.exists(filepath):
        return set()
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    # Simple regex to find keys: 'key' =>
    keys = set(re.findall(r"'([^']+)'\s*=>", content))
    return keys

def append_strings(filepath, new_strings, is_ms=False):
    if not new_strings:
        return
        
    if not os.path.exists(filepath):
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write("<?php\nreturn [\n];\n")
            
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
        
    # Remove the closing ];
    content = re.sub(r'\];\s*$', '', content)
    
    appends = []
    for s in new_strings:
        escaped_key = s.replace("'", "\\'")
        # For MS, we add a prefix so you know it needs manual translation
        val = f"[TRANSLATE] {s}" if is_ms else s
        escaped_val = val.replace("'", "\\'")
        appends.append(f"    '{escaped_key}' => '{escaped_val}',")
        
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content.rstrip() + "\n" + "\n".join(appends) + "\n];\n")

def process_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    strings_found = set()
    
    def replacer(match):
        text = match.group(1)
        clean_text = text.strip()
        
        # Skip empty, pure numbers, or already wrapped, or contains php code
        if not clean_text or not re.search(r'[a-zA-Z]', clean_text) or '<?' in text or '__(' in clean_text:
            return match.group(0)
            
        # Keep original whitespace
        left_space = text[:len(text) - len(text.lstrip())]
        right_space = text[len(text.rstrip()):]
        
        strings_found.add(clean_text)
        escaped_text = clean_text.replace("'", "\\'")
        return f">{left_space}<?= __('{escaped_text}') ?>{right_space}<"

    def placeholder_replacer(match):
        text = match.group(1)
        if text.strip() and not '<?' in text and not '__(' in text:
            strings_found.add(text)
            escaped_text = text.replace("'", "\\'")
            return f'placeholder="<?= __(\'{escaped_text}\') ?>"'
        return match.group(0)

    # 1. Replace text between > and <
    new_content = re.sub(r'>([^<]+)<', replacer, content)
    
    # 2. Replace placeholders
    new_content = re.sub(r'placeholder="([^"]+)"', placeholder_replacer, new_content)

    if content != new_content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f"Updated {filepath} ({len(strings_found)} strings wrapped)")
    
    return strings_found

def main():
    if len(sys.argv) < 2:
        print("Usage: python translate_wrapper.py <file_or_directory>")
        sys.exit(1)
        
    target = sys.argv[1]
    
    existing_en = get_existing_strings(LANG_EN_FILE)
    existing_ms = get_existing_strings(LANG_MS_FILE)
    
    all_new_strings = set()
    
    if os.path.isfile(target):
        files = [target]
    else:
        files = []
        for root, _, filenames in os.walk(target):
            for filename in filenames:
                if filename.endswith('.php') and 'lang' not in root:
                    files.append(os.path.join(root, filename))
                    
    for f in files:
        new_strs = process_file(f)
        all_new_strings.update(new_strs)
        
    # Filter out strings that already exist
    new_en = all_new_strings - existing_en
    new_ms = all_new_strings - existing_ms
    
    if new_en:
        append_strings(LANG_EN_FILE, sorted(new_en), is_ms=False)
        print(f"Added {len(new_en)} new strings to {LANG_EN_FILE}")
        
    if new_ms:
        append_strings(LANG_MS_FILE, sorted(new_ms), is_ms=True)
        print(f"Added {len(new_ms)} new strings to {LANG_MS_FILE}")
        print("Note: The new MS strings have been prefixed with [TRANSLATE].")
    else:
        print("No new strings found to add.")

if __name__ == "__main__":
    main()
