<?php

use Config\OSPOS;

?>

            </div>
        </div>

        <div id="footer">
            <div class="jumbotron push-spaces">
                <strong>
                    © 2010 - <?= date('Y') ?> ·
                    <a href="https://opensourcepos.org" target="_blank">opensourcepos.org</a> ·
                    <?= esc(config('App')->application_version) ?> -
                    <a target="_blank" href="https://github.com/opensourcepos/opensourcepos/commit/<?= esc(config(OSPOS::class)->commit_sha1) ?>">
                        <?= esc(substr(config(OSPOS::class)->commit_sha1, 0, 6)); ?>
                    </a>
                </strong>
            </div>
        </div>
    </body>
</html>
