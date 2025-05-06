# chatbot.py
import sys
import json
from gradio_client import Client

# Get the question from Laravel (passed as argument)
question = sys.argv[1]

# Connect to the Gradio chatbot
client = Client("Gajendra5490/SkinChatBot")
# client = Client("Gajendra5490/chatbot_v2")

# Send the prompt
result = client.predict(
    prompt=question,
    api_name="/predict"
)

# Print the response to be captured by Laravel
print(json.dumps({"response": result}))

