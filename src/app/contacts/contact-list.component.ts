import { CommonModule } from '@angular/common';
import { Component, OnInit, computed, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router, RouterModule } from '@angular/router';
import { finalize } from 'rxjs';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { ContactsApiService } from '../shared/api/contacts-api.service';
import { Contact, ContactQueryParams, PaginationMeta } from '../shared/models/contact.model';

@Component({
    selector: 'app-contact-list',
    standalone: true,
    imports: [CommonModule, FormsModule, RouterModule],
    templateUrl: './contact-list.component.html',
})
export class ContactListComponent implements OnInit {
    private readonly contactsApi = inject(ContactsApiService);
    private readonly router = inject(Router);

    readonly contacts = signal<Contact[]>([]);
    readonly loading = signal(false);
    readonly searchTerm = signal('');
    readonly statusFilter = signal<string | null>(null);
    readonly pagination = signal<PaginationMeta | null>(null);

    readonly showingResults = computed(() => this.contacts().length > 0);

    ngOnInit(): void {
        this.fetchContacts();
    }

    fetchContacts(page?: number): void {
        const params: ContactQueryParams = {
            q: this.searchTerm().trim() || undefined,
            status: this.statusFilter() || undefined,
            page,
        };

        this.loading.set(true);

        this.contactsApi
            .getContacts(params)
            .pipe(takeUntilDestroyed(), finalize(() => this.loading.set(false)))
            .subscribe((response) => {
                this.contacts.set(response.data);
                this.pagination.set(response.meta ?? null);
            });
    }

    onSearch(): void {
        this.fetchContacts();
    }

    clearFilters(): void {
        this.searchTerm.set('');
        this.statusFilter.set(null);
        this.fetchContacts();
    }

    goToContact(contact: Contact): void {
        this.router.navigate(['/contacts', contact.id]);
    }
}
