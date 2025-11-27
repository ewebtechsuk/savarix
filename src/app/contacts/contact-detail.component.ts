import { CommonModule } from '@angular/common';
import { Component, OnInit, inject, signal } from '@angular/core';
import { ActivatedRoute, RouterModule } from '@angular/router';
import { of, switchMap } from 'rxjs';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { ContactsApiService } from '../shared/api/contacts-api.service';
import { Contact } from '../shared/models/contact.model';

export type ContactTab = 'main' | 'applicant' | 'activities' | 'offers' | 'viewings' | 'tasks' | 'documents';

@Component({
    selector: 'app-contact-detail',
    standalone: true,
    imports: [CommonModule, RouterModule],
    templateUrl: './contact-detail.component.html',
})
export class ContactDetailComponent implements OnInit {
    private readonly contactsApi = inject(ContactsApiService);
    private readonly route = inject(ActivatedRoute);

    readonly contact = signal<Contact | null>(null);
    readonly loading = signal(true);
    readonly activeTab = signal<ContactTab>('main');
    readonly tabs: ContactTab[] = ['main', 'applicant', 'activities', 'offers', 'viewings', 'tasks', 'documents'];

    ngOnInit(): void {
        this.route.paramMap
            .pipe(
                switchMap((params) => {
                    const id = Number(params.get('id'));

                    if (!Number.isFinite(id)) {
                        return of(null);
                    }

                    return this.contactsApi.getContact(id);
                }),
                takeUntilDestroyed(),
            )
            .subscribe((contact) => {
                this.contact.set(contact);
                this.loading.set(false);
            });
    }

    selectTab(tab: ContactTab): void {
        this.activeTab.set(tab);
    }
}
