<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exposvre</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            background-color: #000000;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            width: 100%;
        }

        .logo {
            max-width: 75%;
            height: auto;
        }

        .open-app-btn {
            margin-top: 40px;
            padding: 10px 20px;
            font-size: 18px;
            font-weight: 500;
            background-color: #EC008C;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .open-app-btn:hover {
            background-color: #ff1493;
        }
    </style>
</head>
<body>
<div class="container">
    <img src="https://240c3b.p3cdn1.secureserver.net/wp-content/uploads/2023/09/EXPOSURE-Logo-white-on-pink.png?time=1715872980" alt="Logo" class="logo">
    <button class="open-app-btn" onclick="openApp()">Open App</button>
</div>

<script>
    /// Handle with second domain universal link
       function openApp() {
          
            const redirectDeepLinkDomain = "https://apps.apple.com/us/app/exposvre/id6723885004"

            var start = new Date().getTime();
            const path = window.location.pathname
            var deeplinkUrl = redirectDeepLinkDomain;
            
            var appStoreUrl = "https://apps.apple.com/global/app/exposvre/id6723885004";  // Updated URL

            window.location.href = deeplinkUrl;
            
            setTimeout(function() {
                var end = new Date().getTime();
                if (end - start < 1500) {
                    window.location.href = appStoreUrl;                    
                }
            }, 2000);
        }

    /// Handle with regular deeplink
    // function openApp() {

    //     var start = new Date().getTime();
    //     // Path
    //     const path = window.location.pathname.replace("link/", "")
    //     // Deeplink URL
    //     var deeplinkUrl = 'EXPOSVRE://' + path;
    //     // App Store URL
    //     var appStoreUrl = "https://apps.apple.com/us/app/exposvre/id1630178424";

    //     window.location.href = deeplinkUrl;

    //     setTimeout(function() {
    //         var end = new Date().getTime();
    //         if (end - start < 1500) {
    //             // Redirect to the App Store if the deeplink failed
    //             window.location.href = appStoreUrl;
    //         }
    //     }, 2000);
    // }
</script>
</body>
</html>
