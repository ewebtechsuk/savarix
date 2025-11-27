import { inject, Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { map, Observable } from 'rxjs';
import { Contact, ContactPayload, ContactQueryParams, ContactResponse, PaginatedContacts } from '../models/contact.model';

@Injectable({ providedIn: 'root' })
export class ContactsApiService {
    private readonly http = inject(HttpClient);
    private readonly baseUrl = '/api/applicants';

    getContacts(params: ContactQueryParams = {}): Observable<PaginatedContacts> {
        let httpParams = new HttpParams();

        if (params.q) {
            httpParams = httpParams.set('q', params.q);
        }

        if (params.status) {
            httpParams = httpParams.set('status', params.status);
        }

        if (params.page) {
            httpParams = httpParams.set('page', params.page.toString());
        }

        return this.http.get<PaginatedContacts>(this.baseUrl, { params: httpParams });
    }

    getContact(id: number): Observable<Contact> {
        return this.http
            .get<ContactResponse>(`${this.baseUrl}/${id}`)
            .pipe(map((response) => response.data));
    }

    createContact(payload: ContactPayload): Observable<Contact> {
        return this.http
            .post<ContactResponse>(this.baseUrl, payload)
            .pipe(map((response) => response.data));
    }

    updateContact(id: number, payload: ContactPayload): Observable<Contact> {
        return this.http
            .put<ContactResponse>(`${this.baseUrl}/${id}`, payload)
            .pipe(map((response) => response.data));
    }

    deleteContact(id: number): Observable<void> {
        return this.http.delete<void>(`${this.baseUrl}/${id}`);
    }
}
