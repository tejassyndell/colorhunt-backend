import { Component, OnInit } from '@angular/core';
import { Validators, FormBuilder, FormGroup } from '@angular/forms';
import { RouterModule, Routes, Router, ActivatedRoute } from '@angular/router';
import { ToastrService } from 'ngx-toastr';
import { ValidationService } from '../../services/config.service';
import { UserService } from '../../services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { Title } from '@angular/platform-browser';
import { environment } from '../../../environments/environment';


@Component({
  selector: 'app-articlephotos',
  templateUrl: './articlephotos.component.html',
  styleUrls: ['./articlephotos.component.scss']
})


export class ArticlephotosComponent implements OnInit {
  
  public editarray: { ArticleId: number, Images: { photo: string, ArticleId: number }[] } = { ArticleId: 0, Images: [] };

  size: any;
  width: number;
  height: number;
  ss: any;
  selectedFile: any;
  editArticleId: any;
 // uploadedImages: { url: string, file: File }[] = [];
 // ArticleId: '';
  //Images: []; // Initialize an empty array for holding image names
  Images: { photo: string }[] = [];
  errorexit: string = "";
  selectedFileName: string;

  

  
  

  articleimgForm: FormGroup;
  primaryImageIndex:any;
  Orderset: number = 1;
  articlePhotopage: any;
  FileuploadformData: any;
  public articdropdown: any = [];
  accessdenied: boolean = true;
  isList: any;
  isAdd: any;
  isEdit: any;
  isDelete: any;
  ArticleId:any;
  constructor(private formBuilder: FormBuilder, private router: Router, private userService: UserService, private toastr: ToastrService, private route: ActivatedRoute, private spinner: NgxSpinnerService ,private titleService: Title) {
    this.titleService.setTitle("Add Article Photo | Colorhunt");
    this.FileuploadformData = new FormData();
    this.articleimgForm = this.formBuilder.group({
      ArticleId: ['', [Validators.required]],
      Image: ['', [Validators.required]],
    });
    
  }
  

  ngOnInit() {

    const storedPrimaryImageIndex = localStorage.getItem('primaryImageIndex');
    if (storedPrimaryImageIndex !== null) {
      this.primaryImageIndex = +storedPrimaryImageIndex;
    }

    this.userService.articallist().subscribe((res) => {
      this.articdropdown = res;
    });
  
    this.userService.remaininglauncharticle().subscribe((res) => {
      this.articdropdown = res;
    });
  
    const item = JSON.parse(localStorage.getItem('userdata'));
    const rolerights = JSON.parse(localStorage.getItem('roleright'));
  
    if (rolerights && rolerights !== null && rolerights !== undefined) {
      this.rightscheck(rolerights, 1);
    } else {
      this.userService.getroleRights(item[0].Role).subscribe((res) => {
        this.rightscheck(res, 2);
      });
    }
  
    if (!this.accessdenied) {
      this.articlePhotopage = 'Add';
      
      const data = this.route.snapshot.paramMap.get('id');
  
      if (data) {
        this.articlePhotopage = 'Edit';
        this.userService.approvedarticallist(data).subscribe((res) => {
          
          console.log('test', res);
  
          const editedArticle = res[0];
          this.editArticleId = editedArticle.ArticleNumber;
          this.editarray = {
            ArticleId: editedArticle.ArticleNumber,
            Images: JSON.parse(editedArticle.Name).map(image => ({ ...image, ArticleId: editedArticle.ArticleNumber }))
          };
          
          this.FileuploadformData = new FormData(); // Initialize FileuploadformData as FormData
  
          //console.log('test1', this.editarray.Images);
          this.articleimgForm.patchValue(this.editarray);
        });
      }
    }
  }
  
  
  
  rightscheck(data, no) {
    let item = JSON.parse(localStorage.getItem('userdata'));
    if (no == 1) {
      var Count = Object.keys(data).length;
      for (let i = 0; i < Count; i++) {
        if (data[i].PageId == 18) {
          let parameterId = this.route.snapshot.paramMap.get('id');
          if (data[i].AddRights == 1 || data[i].EditRights == 1) {
            if (parameterId == null && data[i].AddRights == 1) {
              this.accessdenied = false;
            } else {
              if (parameterId != null && data[i].EditRights == 1) {
                this.accessdenied = false;
              } else {
                this.accessdenied = true;
              }
            }
          } else {
            this.accessdenied = true;
          }

          this.isList = data[i].ListRights;
          this.isAdd = data[i].AddRights;
          this.isEdit = data[i].EditRights;
          this.isDelete = data[i].DeleteRights;
          break;
        } else {
          this.accessdenied = true;
        }

      }
    } else {
      for (let i = 0; i < data.length; i++) {
        if (data[i].PageId == 18) {
          let parameterId = this.route.snapshot.paramMap.get('id');
          if (data[i].AddRights == 1 || data[i].EditRights == 1) {
            if (parameterId == null && data[i].AddRights == 1) {
              this.accessdenied = false;
            } else {
              if (parameterId != null && data[i].EditRights == 1) {
                this.accessdenied = false;
              } else {
                this.accessdenied = true;
              }
            }
          } else {
            this.accessdenied = true;
          }
          this.isList = data[i].ListRights;
          this.isAdd = data[i].AddRights;
          this.isEdit = data[i].EditRights;
          this.isDelete = data[i].DeleteRights;
          break;
        } else {
          this.accessdenied = true;
        }

      }

    }
  }

  uploadFile(event: any) {
    let elem = event.target;
    var height = 0;
    var width = 0;

    if (elem.files.length > 0) {
      this.FileuploadformData = new FormData();
      for (var i = 0; i < event.target.files.length; i++) {

        this.FileuploadformData.append("myfile[" + i + "]", event.target.files[i]);
      }
      // Display the selected file name
    this.selectedFileName = elem.files[0].name; 
    }
    elem.value = "";
  }
  

  articlephotos() {
    this.spinner.show();
    document.getElementById('submit-button').setAttribute('disabled' ,'true');
    if (this.route.snapshot.paramMap.get('id')) {

      //console.log('sdfsdf::::l',this.editArticleId, '"sdfsdfsdf:', this.route.snapshot.paramMap.get('id'))
      let item = JSON.parse(localStorage.getItem('userdata'));
      //console.log('item',item)
      document.getElementById('submit-button').setAttribute('disabled' ,'true');
      this.FileuploadformData.append("ArticleId", this.route.snapshot.paramMap.get('id'));
      this.FileuploadformData.append("UserId", item[0].Id);
      this.spinner.show();

      
      this.userService.articlephotos(this.FileuploadformData).subscribe( //line8
        (response) => {
          //response code
          this.spinner.hide();
          this.success(response);
          document.getElementById('submit-button').removeAttribute('disabled');
          window.location.reload();
        });
    
    }else {
      
      let item = JSON.parse(localStorage.getItem('userdata'));
      console.log('item',item)
      document.getElementById('submit-button').setAttribute('disabled' ,'true');
      this.FileuploadformData.append("ArticleId", this.articleimgForm.value.ArticleId.Id);
      this.FileuploadformData.append("UserId", item[0].Id);
      this.spinner.show();

      
      this.userService.articlephotos(this.FileuploadformData).subscribe( //line8
        (response) => {
        
          //response code
          this.spinner.hide();
          this.success(response);
          document.getElementById('submit-button').removeAttribute('disabled');
          var id = this.ss
          this.router.navigate(['articlephotos', { id: id }]);
          //window.location.reload();
        });
    
    }
  }

  
  onChangeArticle(event){
    console.log('event',event)
    this.ss = event.Id
  }
  // User Add success function
  success(data) {
    if (data && data.NoMatch === "true") {
      this.toastr.error('Image width and height not matched', 'Failed');
    } else if (data && data.result === "true") {
     
      this.toastr.success('Article Photos Add Successfully', 'Success');
    } else if (data && data.imageRemoved === "true") {
      this.toastr.success('Image removed successfully', 'Success');
    } 
  }
  
  
  

  //user add successupdate function
  
 // successupdate(data) {
   // if (data.NoMatch == "true") {
    //  this.toastr.error('Failed', 'Image width and height not matched');
   // } else if (data.result == "true") {
    //  this.router.navigate(['/articlephotoslist']);
    //  this.toastr.success('Success', 'Article Photos Updated Successfully');
   // } else {
     // this.toastr.error('Failed', 'Please try agin later');
   // }
 // }


  goBack (){
    window.history.back();
  }

  // Inside your component class
  getImageUrl(photoName: string): string {
    const baseUrl = environment.getuploadURL; 
    return baseUrl + photoName;
  }

 // Inside your component class
 removeImage(imageUrl: string) {
  const confirmed = confirm("Are you sure you want to remove this image?");
  if (confirmed) {
    this.userService.deleteImage(imageUrl).subscribe(
      () => {
        // Remove the image from the editarray.Images array
        this.editarray.Images = this.editarray.Images.filter(imageObj => imageObj.photo !== imageUrl);

        // Display a success message for image removal
        this.toastr.success('Image removed successfully', 'Success');
      },
     
      error => {
        console.error("Error removing image:", error);
        this.toastr.error('Failed to remove image. Please try again later', 'Error');
      }
    );
  }
}






updatePrimaryImage(imageObj: any) {
  const confirmed = confirm("Are you sure you want to update this primary image?");
  if (confirmed) {
    const formData = new FormData();

    formData.append('articleId', imageObj.ArticleId.toString());
    formData.append('oldImage', imageObj.photo);

    this.primaryImageIndex = this.editarray.Images.findIndex(img => img.photo === imageObj.photo);

    localStorage.setItem('primaryImageIndex', this.primaryImageIndex.toString());

    // Now you can call your backend API to update the image position
    this.userService.updatePrimaryImage(formData).subscribe(
      (response) => {
        // Handle successful response from the backend
        console.log('Image position updated successfully:', response);
        // You can also update your local data here if needed
      },
      (error) => {
        // Handle error from the backend
        console.error('Error updating image position:', error);
      }
    );
  }
}


}






  
  

