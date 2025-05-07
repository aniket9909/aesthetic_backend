from gradio_client import Client, handle_file
import sys
import json

def main(image_path):
    try:
        client = Client("harshadsalunkhe1212/SkinAnalysis")

        # Use handle_file for local file input
        result = client.predict(
            img=handle_file(image_path),
            api_name="/predict"
        )


        print(json.dumps({"success": True, "message": result}))

    except Exception as e:
        print(json.dumps({"success": False, "error": str(e)}))

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"success": False, "error": "Missing image path"}))
        sys.exit(1)

    image_path = sys.argv[1]
    main(image_path)
