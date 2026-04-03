Config convergence map
======================

Guide rule: keep only feature-specific config/workcore.php and move generic app,
database, auth, cache, queue, mail, and service settings to the host app. This
pack now follows that rule by collapsing all remaining source-side config to a
single config/workcore.php file. Use removed config filenames as lookup hints if
you still need to manually port vendor credentials into MagicAI.
