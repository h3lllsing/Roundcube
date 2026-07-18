<!doctype html>
<html>
<head>
  <meta name="referrer" content="no-referrer">
  <meta http-equiv="X-Frame-Options" content="SAMEORIGIN">
  <meta http-equiv="Content-Security-Policy" content="frame-ancestors 'self'">
  <title>Opening Webmail...</title>
</head>
<body>
  <iframe id="snappy" name="snappy" src="/webmail/" style="width:100%;height:100vh;border:0;"></iframe>

  <form id="tokenForm" method="POST" action="/webmail/plugins/roundcube_portal_auth/receive.php" target="snappy">
    <input type="hidden" name="t" value="{{ $token }}">
  </form>

  <script>document.getElementById('tokenForm').submit();</script>
</body>
</html>
