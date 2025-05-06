<html>
<head>
    <title> OpenTok Getting Started </title>
        <script src="https://static.opentok.com/v2/js/opentok.min.js"></script>
        <style>
        body, html {
    background-color: gray;
    height: 100%;
}

#videos {
    position: relative;
    width: 100%;
    height: 100%;
    margin-left: auto;
    margin-right: auto;
}

#subscriber {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    z-index: 10;
}

#publisher {
    position: absolute;
    width: 360px;
    height: 240px;
    bottom: 10px;
    left: 10px;
    z-index: 100;
    border: 3px solid white;
    border-radius: 3px;
}
    </style>
</head>
<body>

    <div id="videos">
        <div id="subscriber"></div>
        <div id="publisher"></div>
    </div>
    <div id="connectionCountField"></div>

    <script type="text/javascript">
        const apiKey = "{{ $data->apiKey }}";
        const sessionId = "{{ $data->sessionId }}";
        const token = "{{ $data->token }}";
        var connectionCount = 0;
        function handleError(error) {
            if (error) {
                alert(error.message);
            }
        }

        function initializeSession() {
            var session = OT.initSession(apiKey, sessionId);

            // Subscribe to a newly created stream

            // Create a publisher
            var publisher = OT.initPublisher('publisher', {
                insertMode: 'append',
                width: '100%',
                height: '100%'
            }, handleError);

            // Connect to the session
            session.connect(token, function(error) {
                // If the connection is successful, publish to the session
                if (error) {
                handleError(error);
                } else {
                session.publish(publisher, handleError);
                }
            });
            
            session.on('streamCreated', function(event) {
                session.subscribe(event.stream, 'subscriber', {
                    insertMode: 'append',
                    width: '100%',
                    height: '100%'
                }, handleError);
            });
        
        session.on("connectionCreated", function(event) {
   connectionCount++;
   displayConnectionCount();
});
session.on("connectionDestroyed", function(event) {
   connectionCount--;
   displayConnectionCount();
});
session.on("signal", function(event) {
  console.log("Signal sent from connection: " + event);
  console.log(event);
}); 
        }
function displayConnectionCount() {
    console.log(connectionCount.toString());
}
        // (optional) add server code here
        initializeSession();
    </script>
</body>
</html>