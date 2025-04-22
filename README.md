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

    [
      'errors' => [],
      'entity' => 'node:1',
      'paragraph_fields' => [

      ],
      'langs' => [
        'en' => [
          'source' => 'und',
        ],
        'af' => [
          'source' => 'en',
        ],
        'ca' => [
          'source' => 'af',
        ],
      ]
    ]


    drush ev "\Drupal::service('paragraphs_fix_lang_chain')->info('node', 4);"
