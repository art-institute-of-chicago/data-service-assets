import os
import csv

from func import *

# Directory paths, for ease of use
dir_script = os.path.dirname(os.path.realpath(__file__))
dir_repo = os.path.join(dir_script, '..')
dir_storage = os.path.join(dir_repo, 'storage')

dir_data = os.path.join(dir_storage, 'python')

file_lock = os.path.join(dir_data, 'export.lock')

csv_input = os.path.join(dir_data, 'python-input.csv')
csv_output = os.path.join(dir_data, 'python-output.csv')

# Exit if PHP is still exporting stuff
if os.path.isfile(file_lock):
    exit()

# Exit if there's nothing to process yet
if not os.path.isfile(csv_input):
    exit()

# Exit if there is an existing python-output.csv waiting to be digested by PHP
if os.path.isfile(csv_output):
    exit()

file_input = open(csv_input, 'r', newline='\n', encoding='utf-8')
file_output = open(csv_output, 'w+', newline='\n', encoding='utf-8')

keys = [
    'id',
    'ahash',
    'phash',
    'dhash',
    'whash',
    'colorfulness',
]

reader = csv.DictReader(file_input)
writer = csv.DictWriter(file_output, fieldnames=keys)

writer.writeheader()

try:
    for row in reader:

        path_image = row['path']

        if not os.path.isfile(path_image):
            continue

        ahash, phash, dhash, whash = get_image_fingerprint(path_image, row)
        colorfulness = get_image_colorfulness(path_image, row)

        out = {
            'id': row['id'],
            'ahash': ahash,
            'phash': phash,
            'dhash': dhash,
            'whash': whash,
            'colorfulness': colorfulness,
        }

        # Output to CSV and console
        writer.writerow(out)
        print(out)

finally:
    file_input.close()
    file_output.close()

    if os.path.exists(csv_input):
        os.remove(csv_input)
