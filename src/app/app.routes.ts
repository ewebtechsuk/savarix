import { Routes } from '@angular/router';
import { ContactListComponent } from './contacts/contact-list.component';
import { ContactDetailComponent } from './contacts/contact-detail.component';

export const appRoutes: Routes = [
    { path: 'contacts', component: ContactListComponent },
    { path: 'contacts/:id', component: ContactDetailComponent },
];
