Paragraphs Fix Lang Chain
=====

This module proposes a fix for the issue described in [#3519174 When a node in LANG3 is based on the node in LANG2 which is based on LANG1, paragraph fields behave differently depending on whether the paragraph field is added before, or after, the node is translated](https://www.drupal.org/project/paragraphs/issues/3519174).

How it works
-----

### Step 1: set up a "broken" paragraph

Start by following the instructions in the issue above. You should see the following when you visit /ca/node/1:

    paragraphs
    paragraph_field
    paragraph af
    paragraph_field
    paragraph en after translation

instead of the expected

    paragraphs
    paragraph_field
    paragraph af
    paragraph_field
    paragraph af after translation

### Step 2: use this module to fetch information about the "broken" paragraph

    drush ev "\Drupal::service('paragraphs_fix_lang_chain')->info('node', 1);"

You will now see something like this:

    Array
    (
        [entity] => page:1
        [paragraph_fields] => Array
            (
                [0] => field_paragraphs
            )

        [langs] => Array
            (
                [en] => Array
                    (
                        [source] => und
                        [field_values] => Array
                            (
                                [0] => Array
                                    (
                                        [paragraph_id] => 5
                                    )

                                [1] => Array
                                    (
                                        [paragraph_id] => 6
                                    )

                            )

                    )

                [af] => Array
                    (
                        [source] => en
                        [field_values] => Array
                            (
                                [0] => Array
                                    (
                                        [paragraph_id] => 5
                                    )

                                [1] => Array
                                    (
                                        [paragraph_id] => 6
                                    )

                            )

                    )

                [ca] => Array
                    (
                        [source] => af
                        [field_values] => Array
                            (
                                [0] => Array
                                    (
                                        [paragraph_id] => 5
                                    )

                                [1] => Array
                                    (
                                        [paragraph_id] => 6
                                    )

                            )

                    )

            )

    )

You can then see how paragraphs' languages are set up by running:

    drush ev "\Drupal::service('paragraphs_fix_lang_chain')->info('paragraph', 5);"

Which will give you:

    Array
    (
        [entity] => pagragraph:5
        [paragraph_fields] => Array
            (
            )

        [langs] => Array
            (
                [en] => Array
                    (
                        [source] => und
                        [field_values] =>
                    )

                [af] => Array
                    (
                        [source] => en
                        [field_values] =>
                    )

                [ca] => Array
                    (
                        [source] => af
                        [field_values] =>
                    )

            )

    )

or

    drush ev "\Drupal::service('paragraphs_fix_lang_chain')->info('paragraph', 6);"

which will give you

    Array
    (
        [entity] => pagragraph:6
        [paragraph_fields] => Array
            (
            )

        [langs] => Array
            (
                [en] => Array
                    (
                        [source] => und
                        [field_values] =>
                    )

                [af] => Array
                    (
                        [source] => en
                        [field_values] =>
                    )

                [ca] => Array
                    (
                        [source] => en
                        [field_values] =>
                    )

            )

    )

In the above example, the source of paragraph 6 in language ca is "en", not "af" as would be expected by examining the language chain of its parent identity.

You can then simulate a "fix" of the source language of the paragraph "ca" by running:

    drush ev "\Drupal::service('paragraphs_fix_lang_chain')->simulate(6);"

You will see something like this:

    Array
    (
        [child] => pagragraph:6
        [parent] => page:4
        [parent_langchain] => Array
            (
                [en] => und
                [af] => en
                [ca] => af
            )

        [child_langchain] => Array
            (
                [en] => und
                [af] => en
                [ca] => en
            )

        [conclusion] => Changes needed
        [actions] => Array
            (
                [0] => For en, no change needed as the source is the same (und) for the child and parent
                [1] => For af, no change needed as the source is the same (en) for the child and parent
                [2] => For ca, the parent source is af and the child (pagragraph:6) source is en. This should be fixed.
                [3] => SIMULATING changing the lang of paragraph 6's ca to af
            )

    )

When you're ready to perform an actual fix, you can run:

    drush ev "\Drupal::service('paragraphs_fix_lang_chain')->fix(6);"

You will then see:

    Array
    (
        [child] => pagragraph:6
        [parent] => page:4
        [parent_langchain] => Array
            (
                [en] => und
                [af] => en
                [ca] => af
            )

        [child_langchain] => Array
            (
                [en] => und
                [af] => en
                [ca] => en
            )

        [conclusion] => Changes needed
        [actions] => Array
            (
                [0] => For en, no change needed as the source is the same (und) for the child and parent
                [1] => For af, no change needed as the source is the same (en) for the child and parent
                [2] => For ca, the parent source is af and the child (pagragraph:6) source is en. This should be fixed.
                [3] => CHANGED the lang of paragraph 6's ca to af
            )

    )

If you want to undo the fix (because it's fun to then fix it again) you can run:

    drush ev "\Drupal::service('paragraphs_fix_lang_chain')->set(6, 'ca', 'en');"
