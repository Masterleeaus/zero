This placeholder keeps the vendor directory present in version control to satisfy an explicit merge requirement even though vendor is normally untracked.
Runtime dependencies must still be installed via composer.json/composer.lock; all other vendor contents remain ignored and should never be committed.
