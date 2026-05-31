import { z } from 'zod'
import { apiGetList, apiRequest } from '../../../shared/api/client'
import { formSchema, formSubmissionSchema, type Form, type FormSubmission } from '../schemas/form'

export async function listForms(): Promise<Form[]> {
  return apiGetList('/api/v1/forms', formSchema, { auth: true })
}

export async function getForm(slug: string): Promise<Form> {
  return apiRequest({
    method: 'GET',
    path: `/api/v1/forms/${slug}`,
    schema: formSchema,
    auth: true,
  })
}

export async function createForm(body: Record<string, unknown>): Promise<Form> {
  return apiRequest({
    method: 'POST',
    path: '/api/v1/forms',
    body,
    schema: formSchema,
    auth: true,
  })
}

export async function updateForm(slug: string, body: Record<string, unknown>): Promise<Form> {
  return apiRequest({
    method: 'PUT',
    path: `/api/v1/forms/${slug}`,
    body,
    schema: formSchema,
    auth: true,
  })
}

export async function deleteForm(slug: string): Promise<void> {
  await apiRequest({
    method: 'DELETE',
    path: `/api/v1/forms/${slug}`,
    schema: z.null(),
    auth: true,
  })
}

export async function listFormSubmissions(slug: string): Promise<FormSubmission[]> {
  const response = await apiRequest({
    method: 'GET',
    path: `/api/v1/forms/${slug}/submissions`,
    schema: z.object({ data: z.array(formSubmissionSchema) }),
    auth: true,
  })

  return response.data
}
