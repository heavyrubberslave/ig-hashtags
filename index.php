<?php

require_once __DIR__ . '/vendor/autoload.php';

$hashtagBlacklist = (function() {
    $content = file_get_contents('https://raw.githubusercontent.com/heavyrubberslave/ig-hashtags/master/resource/banned');
    return preg_split("/\s+/", $content, -1, PREG_SPLIT_NO_EMPTY);
})();

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Hashtag validator</title>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.10.2/css/all.css" crossorigin="anonymous">
        <style type="text/css">
            textarea.form-control {
                height: 150px;
            }

            .error {
                color: #c00;
            }

            .hashtags dt {
                font-weight: bold;
            }

            .hashtags-blocked .card-header,
            .hashtags-valid .card-header {
                font-size: 1.5em;
            }

            .hashtags-blocked .card-header {
                color: #c00;
            }

            .hashtags-valid .card-header {
                color: green;
            }

            .risky {
                color: orange;
            }

            .approved {
                color: blue;
            }

            .hashtags dd {
                margin: 0;
                padding: 0;
            }

            .note {
                color: #666;
                font-size: 0.7em;
            }
        </style>
    </head>
    <body>
        <header>
            <nav class="navbar navbar-expand-sm navbar-toggleable-sm navbar-dark bg-dark border-bottom box-shadow mb-3">
                <div class="container">
                    <a class="navbar-brand" href="/">Hashtag validator</a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target=".navbar-collapse" aria-controls="navbarSupportedContent"
                            aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="navbar-collapse collapse d-sm-inline-flex flex-sm-row-reverse">
                        <ul class="navbar-nav flex-grow-1">
                            <li class="nav-item">
                                <a class="nav-link" href="/">Home</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>
        <div class="container">
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <div class="form-group">
                    <textarea class="form-control" name="hashtags" placeholder="Enter hashtags to validate..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Validate</button>
            </form>

            <?php

            if (isset($_POST['hashtags'])):
                $hashtagWhitelist = require_once __DIR__ . '/hashtags_whitelist.php';

                $hashtagValidator = new \HRS\HashtagValidator\HashtagValidator($hashtagBlacklist, $hashtagWhitelist);

                try {
                    $result = $hashtagValidator->validate(strip_tags($_POST['hashtags']));
                    $validHashtags = $result['valid'];
                    $blockedHashtags = $result['banned'];
                    $riskyHashtags = $result['risky'];
                } catch (\Exception $e) {
                    echo '<p class="error"><b>' , $e->getMessage() , '</b></p>';
                }
            ?>
            <hr>
            <div class="hashtags-valid card my-3">
                <div class="card-header">
                    Valid hashtags <span class="badge badge-success float-right"><?php echo count($validHashtags); ?></span>
                </div>
                <div class="card-body">
                    <p id="valid-hashtag-content"><?php echo implode(' ', $validHashtags); ?></p>
                    <p><button class="js-copy-to-clipboard btn btn-secondary" data-clipboard-target="#valid-hashtag-content"><i class="fas fa-copy"></i> Copy valid hashtags to clipboard</button></p>
                    <p class="note card-text"><span class="badge badge-warning">Yellow hashtags</span> are considered risky. This means they're not currently on the blacklist but chances are higher that they may be blocked. Check any yellow hashtags and report them to me if they're actually blocked.</p>
                    <p class="note card-text"><span class="badge badge-primary">Blue hashtags</span> are approved and seem to be not banned at the moment they were checked and approved.</p>
                </div>
            </div>

            <div class="hashtags-blocked card my-3">
                <div class="card-header">
                    Banned hashtags <span class="badge badge-danger float-right"><?php echo count($blockedHashtags); ?></span>
                </div>
                <div class="card-body">
                    <p class="card-text"><?php echo count($blockedHashtags) > 0 ? '<span class="badge badge-danger">' . implode('</span> <span class="badge badge-danger">', $blockedHashtags) . '</span>' : 'No banned hashtags were found.'; ?></p>
                </div>
            </div>
            <?php endif; ?>

            <hr>

            <h2>Blacklist</h2>
            <p>Currently there are <b><?php echo count($hashtagBlacklist); ?></b> blocked hashtags.</p>

            <p>
                <a class="btn btn-outline-secondary" data-toggle="collapse" href="#bannedHashtagList" role="button" aria-expanded="false" aria-controls="bannedHashtagList">Show complete list</a>
            </p>
            <div class="collapse" id="bannedHashtagList">
                <div class="card card-body">
                    <p><?php natsort($hashtagBlacklist); echo implode(' ', $hashtagBlacklist); ?></p>
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
        <script src="https://cdn.rawgit.com/zenorocha/clipboard.js/v2.0.4/dist/clipboard.min.js"></script>
        <script type="text/javascript">
            var clip = new ClipboardJS('.js-copy-to-clipboard');
        </script>
    </body>
</html>