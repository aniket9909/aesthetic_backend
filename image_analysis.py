from gradio_client import Client, handle_file
import sys
import json
import concurrent.futures


def main(image_path):
    try:
        # client = Client("harshadsalunkhe1212/skintypes")

        # # # # Use handle_file for local file input
        # result = client.predict(
        #     img=handle_file(image_path),
        #     api_name="/predict"
        # )

        def call_client(model_name, image_path):
            client = Client(model_name)
            return client.predict(
            img=handle_file(image_path),
            api_name="/predict"
            )

        models = ["anujakkulkarni/2ndmodelv7"]

        with concurrent.futures.ThreadPoolExecutor() as executor:
            futures = [executor.submit(call_client, model, image_path) for model in models]
            results = [future.result() for future in concurrent.futures.as_completed(futures)]

        print(json.dumps({"success": True, "message": results}))



    except Exception as e:
        print(json.dumps({"success": False, "error": str(e)}))

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"success": False, "error": "Missing image path"}))
        sys.exit(1)

    image_path = sys.argv[1]
    main(image_path)
