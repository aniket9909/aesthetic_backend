import os
import requests
from gradio_client import Client

# Constants
input_folder = os.path.join(os.getcwd(), "skin_images")
client = Client("https://xintao-gfpgan.hf.space/")

# Receive the ID (can be passed dynamically)
image_id = "665333989624336"  # Example ID: assumes there's a file named `one.png`

# Input image path
print(f"Input folder: {input_folder}")
input_image_path = os.path.join(input_folder, f"{image_id}.png")
print(f"Input image path: {input_image_path}")
# Check if the image exists
if not os.path.exists(input_image_path):
    raise FileNotFoundError(f"Image '{image_id}.png' not found in 'skin_images' folder.")
else:
    print(f"Image '{image_id}.png' found in 'skin_images' folder.")

# Call the model
result = client.predict(
    input_image_path,  # local file path to image
    # 'https://raw.githubusercontent.com/gradio-app/gradio/main/test/test_files/bus.png',
    "v1.4",            # version
    2,                 # rescaling factor
    api_name="/predict"
).result(timeout=120)

# Check if the result is valid
print(f"Result: {result}")

# Output image paths
output_image_path = os.path.join(input_folder, f"after_{image_id}_image.png")
output_file_path = os.path.join(input_folder, f"after_{image_id}_file.jpg")

# Save the returned image
image_response = requests.get(f"file://{result[0]}")
with open(output_image_path, "wb") as img_file:
    img_file.write(image_response.content)

# Save the returned file
file_response = requests.get(f"file://{result[1]}")
with open(output_file_path, "wb") as file:
    file.write(file_response.content)

print(f"Image saved to: {output_image_path}")
print(f"File saved to: {output_file_path}")
