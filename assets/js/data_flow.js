import 'select2'

require('../scss/data-flow.scss')

const $ = require('jquery')

// @see https://symfony.com/doc/current/form/dynamic_form_modification.html#dynamic-generation-for-submitted-forms
$(() => {
  const $preview = $('#data-flow-preview')
  $('[data-run-flow-transform-id]').on('click', function () {
    const id = $(this).data('run-flow-transform-id')
    $preview.attr('src', $preview.data('src').replace('__id__', id))
  })

  $('#new-tranform-transformer').select2({
    placeholder: 'Select transform',
    allowClear: true
  })

  const buildCollectionTypes = (context) => {
    // Collection types
    // @see https://symfony.com/doc/current/reference/forms/types/collection.html#adding-and-removing-items
    $('[data-collection-add-new-widget-selector]', context).on('click', function () {
      const $container = $($(this).data('collection-add-new-widget-selector'))
      let counter = $container.data('widget-counter') || $container.children().length
      const template = $container.attr('data-prototype').replace(/__name__/g, counter)
      counter++
      $container.data('widget-counter', counter)
      const item = $(template)
      $container.append(item)
      buildCollectionTypes(item)
    })

    $('[data-collection-remove-widget-selector]', context).on('click', function () {
      const $container = $($(this).data('collection-remove-widget-selector'))
      $container.remove()
    })
  }

  // @TODO const buildDataTargetForms = (context) => {}

  const $transformer = $('#data_transform_transformer')
  $transformer.on('change', function () {
    const $form = $(this).closest('form')
    // Simulate form data, but only include the selected sport value.
    const data = {
      [$transformer.attr('name')]: $transformer.val(),
      ajax: true
    }

    const $target = $('#data_transform_transformerOptions')
    $target.html('<div class="text-center"><i class="fas fa-spinner fa-pulse fa-3x mr-3"></i><span class="sr-only">loading ...</span></div>')
    // Submit data via AJAX to the form's action path.
    $.ajax({
      url: $form.attr('action'),
      type: $form.attr('method'),
      data: data,
      success: (html) => {
        // Replace current field ...
        $target.replaceWith(
          // ... with the returned one from the AJAX response.
          $(html).find('#data_transform_transformerOptions')
        )
        buildCollectionTypes()
      },
      error: (error) => {
        $target.replaceWith($('<div class="alert alert-danger"/>').html(error))
      }
    })
  })

  buildCollectionTypes()
})

$(function () {
  $('[data-toggle="popover"]').popover()
})
