/**
 * @todo clean this up a little more
 */
(function () {
    /**
     * Adds add/remove buttons for each collection of form fields
     */
    jQuery(document).ready(function () {
        // Get the ul that holds the collection of collectionItems
        var collectionHolder = $('div.field-collection');
        collectionHolder.each(function (index, collection) {
            collection = $(collection);

            // Add a button for each supported key
            var supportedKeys = new String(collection.data('supported-keys')).split(',');
            $.each(supportedKeys, function(index, supportedKey) {
                createAddButon(index, supportedKey, collection);
            });
        });
    });

    /**
     * Creates a add button for each collection item
     *
     * @param index
     * @param supportedKey
     * @param collection
     */
    function createAddButon(index, supportedKey, collection) {
        // setup an "add a collectionItem" link
        var addItemButton = $(
            '<button class="add_collectionItem">Add ' + supportedKey + '</button>'
        );
        var newLinkLi = $('<div></div>').append(addItemButton);

        // add the "add a collectionItem" anchor and li to the collectionItems ul
        collection.append(newLinkLi);

        addItemButton.on('click', function (e) {
            // Prevent button from submitting the form
            e.preventDefault();

            // @todo add delete button or turn this into a toggle button
            // Disable button so item can only be added once
            addItemButton.prop('disabled', true);

            // add a new collectionItem form (see next code block)
            addCollectionItemForm(collection, newLinkLi, supportedKey);
        });
    }

    /**
     * Adds new form item to collection
     *
     * @param collectionHolder
     * @param newLinkLi
     * @param supportedKey
     */
    function addCollectionItemForm(collectionHolder, newLinkLi, supportedKey) {
        // Get the data-prototype explained earlier
        var prototype = collectionHolder.data('prototype');

        var index = supportedKey;

        // Replace placeholders with index
        var newForm = prototype.
            replace(/__name__label__/g, index).
            replace(/__name__/g, index);

        // increase the index with one for the next item
        collectionHolder.data('index', index + 1);

        // Display the form in the page in an li, before the "Add a collectionItem" link li
        var newFormLi = $('<li></li>').append(newForm);
        newLinkLi.before(newFormLi);
    }
})();