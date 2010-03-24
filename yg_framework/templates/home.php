<h1>Yellowgrey Framework</h1>
<p><?php echo $page->link_to_route('example','Example page') ?></p>

<p><?php echo $page->link_to_route('example','AJAX example', 'ajax', null, 'ajax-target') ?> - content will load below</p>
<p id="ajax-target">[Example Page will load here]</p>
