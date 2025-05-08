import os
import requests
import shutil
import sys
import json

from gradio_client import Client

# Constants
input_folder = os.path.join(os.getcwd(), "skin_images")

client = Client("https://xintao-gfpgan.hf.space/")

# image_id = "665333989624336"
image_id = sys.argv[1]



input_image_path = os.path.join(input_folder, f"{image_id}.png")


# Check if the image exists

if not os.path.exists(input_image_path):
    raise FileNotFoundError(f"Image '{image_id}.png' not found in 'skin_images' folder.")

# Call the model

try:
    result = client.predict(
        input_image_path,  # local file path to image
        "v1.4",            # version
        2,                 # rescaling factor
        api_name="/predict"
    )
except Exception as e:
    sys.exit(1)

# Check if the result is valid

output_image_path = os.path.join(input_folder, f"after_{image_id}.png")


# Ensure the result contains valid paths before proceeding
if os.path.exists(result[0]):
    # Copy the file from the temporary location to the destination
    shutil.copy(result[0], output_image_path)
    print(json.dumps({"response": output_image_path}))
else:
    print(json.dumps({"response": "Error: No valid image returned."}))
    sys.exit(1)
