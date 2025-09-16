from gradio_client import Client, handle_file
import sys
import json

def main(image_path):
    try:
        # client = Client("harshadsalunkhe1212/SkinAnalysis")

        # # Use handle_file for local file input
        # result = client.predict(
        #     img=handle_file(image_path),
        #     api_name="/predict"
        # )


        # print(json.dumps({"success": True, "message": result}))
        client = Client("anujakkulkarni/Skin_Type2")
        result1 = client.predict(
                img=handle_file(image_path),
                api_name="/predict"
        )
        client = Client("https://anujakkulkarni-finalist.hf.space/")
        result2 = client.predict(
                image=handle_file(image_path),
                api_name="/predict"
        )
        # combined_result = result
        combined_result = [result1 ,result2]
        print(json.dumps({"success": True, "message": combined_result}))



    except Exception as e:
        print(json.dumps({"success": False, "error": str(e)}))

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"success": False, "error": "Missing image path"}))
        sys.exit(1)

    image_path = sys.argv[1]
    main(image_path)

