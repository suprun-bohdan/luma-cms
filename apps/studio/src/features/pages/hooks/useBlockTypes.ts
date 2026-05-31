import { useMemo } from 'react'
import { useQuery } from '@tanstack/react-query'
import { blockDefinitions, type BlockDefinition } from '../data/blockDefinitions'
import { listEditorBlockTypes, type EditorBlockType } from '../api/blockTypesApi'

function mapApiBlockType(block: EditorBlockType): BlockDefinition {
  return {
    type: block.type,
    label: block.label,
    description: block.description,
    category: 'plugins',
    defaultProps: block.default_props,
    fields: block.fields.map((field) => ({
      name: field.name,
      label: field.label,
      type: field.type as BlockDefinition['fields'][number]['type'],
      placeholder: field.placeholder,
      itemFields: field.item_fields?.map((itemField) => ({
        name: itemField.name,
        label: itemField.label,
        type: itemField.type as 'text' | 'textarea',
      })),
    })),
  }
}

export function useBlockTypes() {
  const query = useQuery({
    queryKey: ['editor', 'block-types'],
    queryFn: listEditorBlockTypes,
    retry: false,
  })

  const definitions = useMemo(() => {
    const pluginBlocks =
      query.data?.filter((block) => block.source === 'plugin').map(mapApiBlockType) ?? []

    return [...blockDefinitions, ...pluginBlocks]
  }, [query.data])

  function getBlockDefinition(type: string): BlockDefinition | undefined {
    return definitions.find((definition) => definition.type === type)
  }

  return {
    ...query,
    definitions,
    getBlockDefinition,
  }
}

export function useBlockPaletteGroups(definitions: BlockDefinition[]) {
  return useMemo(() => {
    const coreGroups = [
      { id: 'content' as const, label: 'Content', description: 'Headlines, copy, features, and FAQs' },
      { id: 'actions' as const, label: 'Actions', description: 'Buttons and conversion sections' },
      { id: 'forms' as const, label: 'Forms', description: 'Embedded forms from the Forms module' },
    ]

    const pluginBlocks = definitions.filter((definition) => definition.category === 'plugins')

    if (pluginBlocks.length === 0) {
      return coreGroups
    }

    return [
      ...coreGroups,
      {
        id: 'plugins' as const,
        label: 'Plugins',
        description: 'Block types registered by enabled plugins',
      },
    ]
  }, [definitions])
}
