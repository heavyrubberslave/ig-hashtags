<?php

namespace HashtagValidator;

class HashtagValidator
{

    private const HASHTAG_PATTERN = '/(?:^|\s)?([#＃][\w\x{05be}\x{05f3}\x{05f4}]*[\p{L}_]+[\w\x{05be}\x{05f3}\x{05f4}]*)/um';

    private $hashtagBlacklist;

    private $hashtagWhitelist;

    public function __construct(array $hashtagBlacklist, array $hashtagWhitelist = [])
    {
        $this->hashtagBlacklist = $hashtagBlacklist;
        $this->hashtagWhitelist = $hashtagWhitelist;
    }

    public function validate(string $hashtags): array
    {
        $matches = null;
        $matchCount = preg_match_all(self::HASHTAG_PATTERN, $hashtags, $matches);

        if (false === $matchCount) {
            $pregError = array_flip(get_defined_constants(true)['pcre'])[preg_last_error()];
            throw new \InvalidArgumentException('Could not parse hashtags: ' . $pregError);
        }

        $parsedHashtags = $matches[1];
        $validHashtags = [];
        $blockedHashtags = [];
        $riskyHashtags = [];
        $approvedHashtags = [];

        foreach ($parsedHashtags as $hashtag) {
            $hashtagWithoutHash = substr($hashtag, 1);

            if (true === in_array($hashtagWithoutHash, $this->hashtagBlacklist, true)) {
                $blockedHashtags[] = $hashtag;
            } else {
                $cssClass = 'badge-light';

                foreach ($this->hashtagBlacklist as $blockedHashtag) {
                    if (false !== strpos($hashtag, $blockedHashtag)) {
                        $riskyHashtags[] = $hashtag;
                        $cssClass = 'badge-warning';
                        break;
                    }
                }

                if (isset($this->hashtagWhitelist[$hashtagWithoutHash])) {
                    $approvedHashtags[] = $hashtag;
                    $cssClass = 'badge-primary';
                }

                $validHashtags[] = '<span class="badge ' . $cssClass . '">' . $hashtag . '</span>';
            }
        }

        return ['valid' => $validHashtags, 'banned' => $blockedHashtags, 'risky' => $riskyHashtags, 'approved' => $approvedHashtags];
    }
}
